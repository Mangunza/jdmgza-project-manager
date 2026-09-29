import { useState } from "react";
import type { FormEvent } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import { useAuth } from "@jm/auth";
import { Alert, Button, Input } from "@jm/ui";

import "./styles.css";

export default function LoginPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const { login } = useAuth();

  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    setError("");
    setLoading(true);

    try {
      await login({
        email,
        password,
      });

      const redirectTo = location.state?.from?.pathname ?? "/dashboard";

      navigate(redirectTo, { replace: true });
    } catch {
      setError(
        "Não foi possível iniciar sessão. Verifique o email e a palavra-passe.",
      );
    } finally {
      setLoading(false);
    }
  }

  return (
    <section className="auth-page">
      <div className="auth-card">
        <div className="auth-card__header">
          <div className="auth-card__mark" aria-hidden="true">
            JM
          </div>

          <h1>Bem-vindo de volta</h1>

          <p>Aceda à sua conta para continuar a gerir os seus projetos.</p>
        </div>

        {error && (
          <Alert variant="danger" className="auth-alert">
            {error}
          </Alert>
        )}

        <form className="auth-form" onSubmit={handleSubmit}>
          <div className="auth-field">
            <label htmlFor="email">Email</label>

            <Input
              id="email"
              name="email"
              type="email"
              autoComplete="email"
              value={email}
              onChange={(event) => setEmail(event.target.value)}
              placeholder="nome@empresa.com"
              required
            />
          </div>

          <div className="auth-field">
            <div className="auth-field__label">
              <label htmlFor="password">Palavra-passe</label>

              <Link to="/forgot-password">Esqueceu a palavra-passe?</Link>
            </div>

            <div className="auth-password">
              <Input
                id="password"
                name="password"
                type={showPassword ? "text" : "password"}
                autoComplete="current-password"
                value={password}
                onChange={(event) => setPassword(event.target.value)}
                placeholder="Introduza a sua palavra-passe"
                required
              />

              <button
                type="button"
                className="auth-password__toggle"
                onClick={() => setShowPassword((current) => !current)}
                aria-label={
                  showPassword
                    ? "Ocultar palavra-passe"
                    : "Mostrar palavra-passe"
                }
              >
                {showPassword ? "Ocultar" : "Mostrar"}
              </button>
            </div>
          </div>

          <Button className="auth-submit" type="submit" disabled={loading}>
            {loading ? "A entrar..." : "Iniciar sessão"}
          </Button>
        </form>

        <div className="auth-card__footer">
          <span>Ainda não tem uma conta?</span>

          <Link to="/register">Criar conta</Link>
        </div>
      </div>
    </section>
  );
}
