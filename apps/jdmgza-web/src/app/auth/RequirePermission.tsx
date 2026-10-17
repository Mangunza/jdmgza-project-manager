import { Navigate, Outlet, useLocation } from "react-router-dom";
import { useAuth } from "@jm/auth";

interface RequirePermissionProps {
  permission: string;
  redirectTo?: string;
}

export default function RequirePermission({
  permission,
  redirectTo = "/dashboard",
}: RequirePermissionProps) {
  const { hasPermission, loading } = useAuth();
  const location = useLocation();

  if (loading) {
    return (
      <section>
        <p>A verificar permissões...</p>
      </section>
    );
  }

  if (!hasPermission(permission)) {
    return (
      <Navigate
        to={redirectTo}
        replace
        state={{ from: location }}
      />
    );
  }

  return <Outlet />;
}
