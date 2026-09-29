import { useState } from "react";
import type { FormEvent } from "react";
import { Link, useNavigate, useSearchParams } from "react-router-dom";
import { resetPassword } from "@jm/api";

import "./styles.css";

export default function ResetPasswordPage() {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();

  const token = searchParams.get("token") ?? "";
  const email = searchParams.get("email") ?? "";

  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] =
    useState("");
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const [loading, setLoading] = useState(false);

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    setError("");
    setSuccess("");

    if (!token || !email) {
      setError(
        "O link de recuperação é inválido ou está incompleto.",
      );
      return;
    }

    if (password !== passwordConfirmation) {
      setError("As palavras-passe não coincidem.");
      return;
    }

    setLoading(true);

    try {
      await resetPassword({
        token,
        email,
        password,
        password_confirmation: passwordConfirmation,
      });

      setSuccess(
        "A sua palavra-passe foi redefinida com sucesso. Será encaminhado para o início de sessão.",
      );

      window.setTimeout(() => {
        navigate("/login");
      }, 1500);
    } catch {
      setError(
        "Não foi possível redefinir a palavra-passe. O link pode ter expirado ou os dados serem inválidos.",
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

          <h1>Nova palavra-passe</h1>
          <p>
            Defina uma nova palavra-passe para recuperar o acesso à
            sua conta.
          </p>
        </div>

        {error && (
          <div className="auth-alert auth-alert--error" role="alert">
            {error}
          </div>
        )}

        {success && (
          <div className="auth-alert auth-alert--success" role="status">
            {success}
          </div>
        )}

        <form className="auth-form" onSubmit={handleSubmit}>
          <div className="auth-field">
            <label htmlFor="reset-password">Nova palavra-passe</label>
            <input
              id="reset-password"
              name="password"
              type="password"
              autoComplete="new-password"
              value={password}
              onChange={(event) => setPassword(event.target.value)}
              placeholder="Nova palavra-passe"
              required
              minLength={8}
            />
          </div>

          <div className="auth-field">
            <label htmlFor="reset-password-confirmation">
              Confirmar palavra-passe
            </label>
            <input
              id="reset-password-confirmation"
              name="password_confirmation"
              type="password"
              autoComplete="new-password"
              value={passwordConfirmation}
              onChange={(event) =>
                setPasswordConfirmation(event.target.value)
              }
              placeholder="Repita a palavra-passe"
              required
              minLength={8}
            />
          </div>

          <button
            className="auth-submit"
            type="submit"
            disabled={loading}
          >
            {loading ? "A redefinir..." : "Redefinir palavra-passe"}
          </button>
        </form>

        <div className="auth-card__footer">
          <Link to="/login">← Voltar para o início de sessão</Link>
        </div>
      </div>
    </section>
  );
}