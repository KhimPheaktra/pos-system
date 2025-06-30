import {createBrowserRouter} from 'react-router-dom';
import DefaultLayout from './components/defaultLayout';
import GustLayout from './components/gustLayout';
import Login from './views/auth/login/login';
import Home from './views/home/home';


const router = createBrowserRouter ([
    {
        path: '/',
        element: <DefaultLayout />,
        children: [
            {
                path: '/category',

            },
            {
                path: '/home',
                element: <Home />,
            }
        ]
    },
    
    {
        path: '/',
        element: <GustLayout />,
        children: [
            {
                path: 'login',
                element: <Login />
            }
        ]
    }
])


export default router
