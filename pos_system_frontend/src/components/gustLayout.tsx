import { Navigate, Outlet } from "react-router-dom";
import { useStateContext } from "../views/context/contextProvider";


export default function GustLayout(){

  const { user } = useStateContext();
if (user) {
    // ✅ If user is already logged in, redirect to home
    return <Navigate to="/home" />;
  }
  return (
      <div className="min-h-screen bg-gray-50">
      {/* No navbar for guest pages */}
      <main className="flex-1">
        <div className="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
          <Outlet />
        </div>
      </main>
    </div>
  );

}