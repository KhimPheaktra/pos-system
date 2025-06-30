import axios, {
  AxiosError,
  type AxiosResponse,
  type InternalAxiosRequestConfig,
} from 'axios';

// Use relative base URL proxy for /api and /refresh-token
const axiosClient = axios.create({
  baseURL: '/', // relative to frontend origin
  headers: {
    'Content-Type': 'application/json',
  },
  withCredentials: true, // sending cookies cross-origin via proxy
});

let accessToken: string | null = null;

export const setAccessToken = (token: string | null) => {
  accessToken = token;
};

let isRefreshing = false;
let failedQueue: {
  resolve: (value: any) => void;
  reject: (reason?: any) => void;
}[] = [];

const processQueue = (error: any, token: string | null = null) => {
  failedQueue.forEach(({ resolve, reject }) => {
    if (error) reject(error);
    else resolve(token);
  });
  failedQueue = [];
};

// Add access token to headers on each request
axiosClient.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    if (accessToken) {
      config.headers = config.headers || {};
      config.headers['Authorization'] = `Bearer ${accessToken}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Handle 401 responses and refresh token logic
axiosClient.interceptors.response.use(
  (response: AxiosResponse) => response,
  async (error: AxiosError) => {
    const originalRequest = error.config as InternalAxiosRequestConfig & { _retry?: boolean };

    // Avoid infinite loop if refresh-token endpoint fails or original request is the refresh endpoint itself
    if (
      error.response?.status === 401 &&
      !originalRequest._retry &&
      !originalRequest.url?.includes('/refresh-token')
    ) {
      if (isRefreshing) {
        // Queue the request until refresh finished
        return new Promise((resolve, reject) => {
          failedQueue.push({ resolve, reject });
        }).then((token) => {
          originalRequest.headers = originalRequest.headers || {};
          originalRequest.headers['Authorization'] = `Bearer ${token}`;
          return axiosClient(originalRequest);
        });
      }

      originalRequest._retry = true;
      isRefreshing = true;

      try {
        const refreshResponse = await axios.post<{ token: string }>(
          '/refresh-token',
          {},
          { withCredentials: true }
        );
        const newToken = refreshResponse.data.token;

        setAccessToken(newToken);
        processQueue(null, newToken);

        originalRequest.headers = originalRequest.headers || {};
        originalRequest.headers['Authorization'] = `Bearer ${newToken}`;

        return axiosClient(originalRequest);
      } catch (refreshError) {
        processQueue(refreshError, null);
        if (typeof window !== 'undefined') {
          window.location.href = '/login'; // redirect to login on refresh failure
        }
        return Promise.reject(refreshError);
      } finally {
        isRefreshing = false;
      }
    }

    return Promise.reject(error);
  }
);

export default axiosClient;
