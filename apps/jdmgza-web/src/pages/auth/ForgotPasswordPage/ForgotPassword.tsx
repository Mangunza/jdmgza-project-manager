import { useState } from "react";
import type { FormEvent } from "react";
import { Link } from "react-router-dom";
import { ApiError, forgotPassword } from "@jm/api";

import "./styles.css";

export default function ForgotPasswordPage() {
  const [email, setEmail] = useState("");
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    setMessage("");
    setError("");
    setLoading(true);

    try {
      await forgotPassword({ email });

      setMessage(
        "Se existir uma conta associada a este email, receberá instruções para redefinir a palavra-passe.",
      );
    } catch (cause: unknown) {
      if (cause instanceof ApiError) {
        const validationMessages = Object.values(
          cause.validationErrors,
        ).flat();

        if (validationMessages.length > 0) {
          setError(validationMessages.join(" "));
        } else {
          setError(cause.message);
        }
      } else {
        setError("Não foi possível processar o pedido. Tente novamente.");
      }
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

          <h1>Recuperar acesso</h1>
          <p>
            Introduza o seu email e enviaremos as instruções
            necessárias para redefinir a sua palavra-passe.
          </p>
        </div>

        {message && (
          <div className="auth-alert auth-alert--success" role="status">
            {message}
          </div>
        )}

        {error && (
          <div className="auth-alert auth-alert--error" role="alert">
            {error}
          </div>
        )}

        <form className="auth-form" onSubmit={handleSubmit}>
          <div className="auth-field">
            <label htmlFor="forgot-email">Email</label>
            <input
              id="forgot-email"
              name="email"
              type="email"
              autoComplete="email"
              value={email}
              onChange={(event) => setEmail(event.target.value)}
              placeholder="nome@empresa.com"
              required
            />
          </div>

          <button
            className="auth-submit"
            type="submit"
            disabled={loading}
          >
            {loading ? "A processar..." : "Enviar instruções"}
          </button>
        </form>

        <div className="auth-card__footer">
          <Link to="/login">← Voltar para o início de sessão</Link>
        </div>
      </div>
    </section>
  );
}