import { useState } from "react";
import type { FormEvent } from "react";
import { Link, useNavigate } from "react-router-dom";
import { ApiError } from "@jm/api";
import { useAuth } from "@jm/auth";

import "./styles.css";

export default function RegisterPage() {
  const navigate = useNavigate();
  const { register } = useAuth();

  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");

  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError("");

    if (password !== passwordConfirmation) {
      setError("As palavras-passe não coincidem.");
      return;
    }

    setLoading(true);

    try {
      await register({
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
      });

      navigate("/dashboard");
    } catch (cause: unknown) {
      if (cause instanceof ApiError) {
        const validationMessages = Object.values(
          cause.validationErrors,
        ).flat();

        if (validationMessages.length > 0) {
          setError(validationMessages.join(" "));
        } else {
          setError(
            cause.message ||
              "Não foi possível criar a conta. Verifique os dados introduzidos e tente novamente.",
          );
        }
      } else {
        setError(
          "Não foi possível criar a conta. Verifique os dados introduzidos e tente novamente.",
        );
      }
    } finally {
      setLoading(false);
    }
  }

  return (
    <section className="auth-page">
      <div className="auth-card auth-card--wide">
        <div className="auth-card__header">
          <div className="auth-card__mark" aria-hidden="true">
            JM
          </div>

          <h1>Criar uma conta</h1>
          <p>Crie a sua conta para começar a gerir projetos.</p>
        </div>

        {error && (
          <div className="auth-alert auth-alert--error" role="alert">
            {error}
          </div>
        )}

        <form className="auth-form" onSubmit={handleSubmit}>
          <div className="auth-field">
            <label htmlFor="name">Nome</label>
            <input
              id="name"
              name="name"
              type="text"
              autoComplete="name"
              value={name}
              onChange={(event) => setName(event.target.value)}
              placeholder="O seu nome"
              required
            />
          </div>

          <div className="auth-field">
            <label htmlFor="register-email">Email</label>
            <input
              id="register-email"
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
            <label htmlFor="register-password">Palavra-passe</label>
            <input
              id="register-password"
              name="password"
              type="password"
              autoComplete="new-password"
              value={password}
              onChange={(event) => setPassword(event.target.value)}
              placeholder="Crie uma palavra-passe"
              required
              minLength={8}
            />
          </div>

          <div className="auth-field">
            <label htmlFor="password-confirmation">
              Confirmar palavra-passe
            </label>
            <input
              id="password-confirmation"
              name="password_confirmation"
              type="password"
              autoComplete="new-password"
              value={passwordConfirmation}
              onChange={(event) => setPasswordConfirmation(event.target.value)}
              placeholder="Repita a palavra-passe"
              required
              minLength={8}
            />
          </div>

          <button className="auth-submit" type="submit" disabled={loading}>
            {loading ? "A criar conta..." : "Criar conta"}
          </button>
        </form>

        <div className="auth-card__footer">
          <span>Já tem uma conta?</span>
          <Link to="/login">Iniciar sessão</Link>
        </div>
      </div>
    </section>
  );
}
