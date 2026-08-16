import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useState,
  type ReactNode,
} from "react";

import {
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

  const refreshUser = useCallback(async () => {
    const token = localStorage.getItem("jm_auth_token");

    if (!token) {
      setUser(null);
      setLoading(false);
      return;
    }

    try {
      const response = await apiMe();

      setUser(response.user);
    } catch {
      localStorage.removeItem("jm_auth_token");
      setUser(null);
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
      await apiLogout();
    } finally {
      setUser(null);
    }
  }, []);

  const value: AuthContextValue = {
    user,
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
