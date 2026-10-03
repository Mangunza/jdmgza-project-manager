import { Navigate, Outlet, useLocation } from "react-router-dom";

import { ApiError } from "@jm/api";
import { useAuth } from "@jm/auth";

export default function RequireAuth() {
  const { authError, isAuthenticated, loading } = useAuth();
  const location = useLocation();

  if (authError) {
    const message =
      authError instanceof ApiError
        ? authError.message
        : "Não foi possível verificar a autenticação.";

    return (
      <section>
        <p>{message}</p>
        <button type="button" onClick={() => window.location.reload()}>
          Tentar novamente
        </button>
      </section>
    );
  }

  if (loading) {
    return (
      <section>
        <p>A verificar autenticação...</p>
      </section>
    );
  }

  if (!isAuthenticated) {
    return (
      <Navigate
        to="/login"
        replace
        state={{ from: location }}
      />
    );
  }

  return <Outlet />;
}
