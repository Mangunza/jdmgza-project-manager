import {
  useState,
} from "react";

import type {
  FormEvent,
} from "react";

import {
  useLocation,
  useNavigate,
} from "react-router-dom";

import { useAuth } from "../../app/auth/AuthProvider";

interface LoginLocationState {
  from?: {
    pathname?: string;
  };
}

export default function Login() {
  const navigate = useNavigate();
  const location = useLocation();

  const { login, loading } = useAuth();

  const state = location.state as LoginLocationState | null;

  const redirectTo =
    state?.from?.pathname ?? "/dashboard";

  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");

  const [error, setError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  async function handleSubmit(
    event: FormEvent<HTMLFormElement>,
  ) {
    event.preventDefault();

    setError(null);
    setSubmitting(true);

    try {
      await login({
        email,
        password,
      });

      navigate(redirectTo, {
        replace: true,
      });
    } catch {
      setError(
        "Não foi possível efetuar o login. Verifique o email e a palavra-passe.",
      );
    } finally {
      setSubmitting(false);
    }
  }

  if (loading) {
    return (
      <section>
        <p>A verificar autenticação...</p>
      </section>
    );
  }

  return (
    <section>
      <h2>Login</h2>

      <form onSubmit={handleSubmit}>
        <div>
          <label htmlFor="email">
            Email
          </label>

          <input
            id="email"
            type="email"
            value={email}
            onChange={(event) =>
              setEmail(event.target.value)
            }
            required
            autoComplete="email"
          />
        </div>

        <div>
          <label htmlFor="password">
            Palavra-passe
          </label>

          <input
            id="password"
            type="password"
            value={password}
            onChange={(event) =>
              setPassword(event.target.value)
            }
            required
            autoComplete="current-password"
          />
        </div>

        {error && (
          <p role="alert">
            {error}
          </p>
        )}

        <button
          type="submit"
          disabled={submitting}
        >
          {submitting
            ? "A entrar..."
            : "Entrar"}
        </button>
      </form>
    </section>
  );
}
