
import React, {
  useState,
  type ChangeEvent,
  type FormEvent,
  type MouseEvent,
  type FocusEvent,
} from 'react';
import './Login.css';
import { useStateContext } from '../../context/contextProvider';
import axiosClient, { setAccessToken } from '../../../axiosClient';
import { useNavigate } from 'react-router-dom';

interface FormData {
  email: string;
  password: string;
  remember: boolean;
}

interface FormErrors {
  email?: string;
  password?: string;
}

interface LoginProps {
  onLogin?: (formData: FormData) => void;
}

const Login: React.FC<LoginProps> = ({ onLogin }) => {
  const { setUser } = useStateContext();
  const navigate = useNavigate();

  const [formData, setFormData] = useState<FormData>({
    email: '',
    password: '',
    remember: false,
  });

  const [errors, setErrors] = useState<FormErrors>({});
  const [statusMessage, setStatusMessage] = useState<string>('');
  const [isLoading, setIsLoading] = useState<boolean>(false);

  const handleInputChange = (e: ChangeEvent<HTMLInputElement>): void => {
    const { name, value, type, checked } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value,
    }));

    if (errors[name as keyof FormErrors]) {
      setErrors((prev) => ({
        ...prev,
        [name]: undefined,
      }));
    }
  };

  const validateForm = (): FormErrors => {
    const newErrors: FormErrors = {};
    if (!formData.email) newErrors.email = 'Email is required';
    else if (!/\S+@\S+\.\S+/.test(formData.email)) newErrors.email = 'Email is invalid';
    if (!formData.password) newErrors.password = 'Password is required';
    else if (formData.password.length < 6) newErrors.password = 'Password must be at least 6 characters';
    return newErrors;
  };

    const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const errors = validateForm();
    if (Object.keys(errors).length > 0) {
        setErrors(errors);
        return;
    }

    try {
        setIsLoading(true);
        setStatusMessage('Signing in...');
        const payload = {
        email: formData.email,
        password: formData.password,
        remember: formData.remember,
        };

        const response = await axiosClient.post('/api/login', payload);

        const token = response.data.token;
        const user = response.data.user;

        setAccessToken(token);
        setUser(user);

        setStatusMessage('Login successful!');
        navigate('/home');

        if (onLogin) onLogin(formData);
    } catch (error: any) {
        console.error('Login error:', error);
        const msg = error.response?.data?.message || 'Login failed. Please try again.';
        setStatusMessage(msg);
        setUser(null);
    } finally {
        setIsLoading(false);
    }
    };


  const createRippleEffect = (e: MouseEvent<HTMLButtonElement>): void => {
    const button = e.currentTarget;
    const ripple = document.createElement('span');
    const rect = button.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = e.clientX - rect.left - size / 2 + 'px';
    ripple.style.top = e.clientY - rect.top - size / 2 + 'px';
    ripple.className = 'ripple-effect';
    button.appendChild(ripple);
    setTimeout(() => ripple.remove(), 600);
  };

  const handleInputFocus = (e: FocusEvent<HTMLInputElement>): void => {
    const formGroup = e.target.closest('.form-group');
    formGroup?.classList.add('focused');
  };

  const handleInputBlur = (e: FocusEvent<HTMLInputElement>): void => {
    const formGroup = e.target.closest('.form-group');
    formGroup?.classList.remove('focused');
  };

  const getStatusMessageClass = (): string => {
    if (statusMessage.includes('successful')) return 'status-message success';
    if (statusMessage.includes('failed')) return 'status-message error';
    return 'status-message info';
  };

  return (
    <div className="login-wrapper">
      <div className="login-container">
        <div className="logo-section">
          <div className="logo-placeholder">L</div>
          <h1 className="welcome-text">Welcome Back</h1>
          <p className="subtitle">Sign in to your account</p>
        </div>

        {statusMessage && <div className={getStatusMessageClass()}>{statusMessage}</div>}

        <form className="login-form" onSubmit={handleSubmit}>
          <div className={`form-group ${errors.email ? 'has-error' : ''}`}>
            <label htmlFor="email" className="form-label">Email Address</label>
            <input
              id="email"
              type="email"
              name="email"
              className="form-input"
              value={formData.email}
              onChange={handleInputChange}
              onFocus={handleInputFocus}
              onBlur={handleInputBlur}
              required
              autoFocus
              autoComplete="username"
              placeholder="Enter your email"
              disabled={isLoading}
            />
            {errors.email && <div className="error-message">{errors.email}</div>}
          </div>

          <div className={`form-group ${errors.password ? 'has-error' : ''}`}>
            <label htmlFor="password" className="form-label">Password</label>
            <input
              id="password"
              type="password"
              name="password"
              className="form-input"
              value={formData.password}
              onChange={handleInputChange}
              onFocus={handleInputFocus}
              onBlur={handleInputBlur}
              required
              autoComplete="current-password"
              placeholder="Enter your password"
              disabled={isLoading}
            />
            {errors.password && <div className="error-message">{errors.password}</div>}
          </div>

          <div className="remember-me">
            <input
              id="remember_me"
              type="checkbox"
              name="remember"
              className="checkbox-input"
              checked={formData.remember}
              onChange={handleInputChange}
              disabled={isLoading}
            />
            <label htmlFor="remember_me" className="checkbox-label">Remember me</label>
          </div>

          <button
            type="submit"
            className={`login-button ${isLoading ? 'loading' : ''}`}
            onClick={createRippleEffect}
            disabled={isLoading}
          >
            {isLoading ? 'Signing In...' : 'Sign In'}
          </button>
        </form>
      </div>
    </div>
  );
};

export default Login;
