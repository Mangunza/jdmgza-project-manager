import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useState,
  type ReactNode,
} from "react";

import {
  ApiError,
  login as apiLogin,
  register as apiRegister,
  me as apiMe,
  logout as apiLogout,
} from "@jm/api";

import type {
  AuthUser,
  LoginPayload,
  RegisterPayload,
} from "@jm/api";

interface AuthContextValue {
  user: AuthUser | null;
  loading: boolean;
  authError: Error | null;
  isAuthenticated: boolean;
  login: (payload: LoginPayload) => Promise<AuthUser>;
  register: (payload: RegisterPayload) => Promise<AuthUser>;
  logout: () => Promise<void>;
  refreshUser: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | undefined>(
  undefined,
);

interface AuthProviderProps {
  children: ReactNode;
}

export function AuthProvider({
  children,
}: AuthProviderProps) {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [loading, setLoading] = useState(true);
  const [authError, setAuthError] = useState<Error | null>(null);

  const refreshUser = useCallback(async () => {
    const token = localStorage.getItem("jm_auth_token");

    if (!token) {
      setAuthError(null);
      setUser(null);
      setLoading(false);
      return;
    }

    try {
      setAuthError(null);
      const response = await apiMe();

      setUser(response.user);
    } catch (cause) {
      if (cause instanceof ApiError && cause.isUnauthorized) {
        localStorage.removeItem("jm_auth_token");
        setUser(null);
        setAuthError(null);
      } else {
        const nextError =
          cause instanceof Error
            ? cause
            : new Error("Não foi possível verificar a autenticação.");

        setAuthError(nextError);
      }
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    void refreshUser();
  }, [refreshUser]);

  const login = useCallback(
    async (payload: LoginPayload): Promise<AuthUser> => {
      const response = await apiLogin(payload);

      setUser(response.user);

      return response.user;
    },
    [],
  );

  const register = useCallback(
    async (payload: RegisterPayload): Promise<AuthUser> => {
      const response = await apiRegister(payload);

      setUser(response.user);

      return response.user;
    },
    [],
  );

  const logout = useCallback(async (): Promise<void> => {
    try {
      setAuthError(null);
      await apiLogout();
    } finally {
      localStorage.removeItem("jm_auth_token");
      setUser(null);
    }
  }, []);

  const value: AuthContextValue = {
    user,
    authError,
    loading,
    isAuthenticated: user !== null,
    login,
    register,
    logout,
    refreshUser,
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth(): AuthContextValue {
  const context = useContext(AuthContext);

  if (!context) {
    throw new Error(
      "useAuth deve ser usado dentro de um AuthProvider.",
    );
  }

  return context;
}
