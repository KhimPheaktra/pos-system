import axios from 'axios';


const BASE_URL = import.meta.env.REACT_APP_API_URL 
const axiosClient = axios.create({
 baseURL: BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

axiosClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      try {
        await axiosClient.post('/refresh'); 
        return axiosClient(error.config); 
      } catch (refreshError) {
        // refresh failed logout
        window.location.href = '/login';
      }
    }
    return Promise.reject(error);
  }
);

export default axiosClient;
