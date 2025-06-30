import { Navigate, Outlet } from "react-router-dom";
import { useStateContext } from "../views/context/contextProvider";
import Home from "../views/home/home";


export default function DefaultLayout(){
 const { user } = useStateContext();
    if(!user){
       return <Navigate to='/login'/>
    }
        return(
        <div>
            <div>
                <Home />
            </div>
            <Outlet/>
        </div>
    )
}
