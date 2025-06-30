import { createContext, useContext, useState, useEffect, type ReactNode } from "react";
import axiosClient, { setAccessToken } from "../../axiosClient";

interface User {
  email: string;
  password: string;
}

interface State {
  user: User | null;
  setUser: (u: User | null) => void;
  isAuthenticated: boolean;
}

export const StateContext = createContext<State>({
  user: null,
  setUser: () => {},
  isAuthenticated: false,
});

interface Props {
  children: ReactNode;
}

// Global flag to avoid duplicate refresh calls in React StrictMode
let hasTriedRefreshGlobal = false;

export const ContextProvider = ({ children }: Props) => {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

    useEffect(() => {
    if (hasTriedRefreshGlobal) {
        setLoading(false);
        return;
    }
    hasTriedRefreshGlobal = true;

    const tryRefresh = async () => {
        try {
        // Call refresh token endpoint, sending cookie automatically
        const response = await axiosClient.post('/api/refresh-token', {}, { withCredentials: true });
        const newAccessToken = response.data.token;

        setAccessToken(newAccessToken);

        // Fetch current user info with new access token
        const meResponse = await axiosClient.get('/api/user', { withCredentials: true });
        setUser(meResponse.data);

        } catch (err: any) {
        if (err.response?.status === 401) {
            // No valid refresh token or not logged in
            setUser(null);
        } else {
            console.error('Refresh token failed:', err);
        }
        } finally {
        setLoading(false);
        }
    };

    tryRefresh();
    }, []);
  if (loading) {
    return <div>Loading...</div>; // Optionally replace with a spinner
  }

  return (
    <StateContext.Provider
      value={{
        user,
        setUser,
        isAuthenticated: !!user,
      }}
    >
      {children}
    </StateContext.Provider>
  );
};

export const useStateContext = () => useContext(StateContext);
