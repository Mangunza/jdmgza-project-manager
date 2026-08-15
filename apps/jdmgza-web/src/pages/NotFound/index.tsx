import { Link } from "react-router-dom";

export default function NotFound() {
  return (
    <section>
      <h2>404</h2>

      <p>Página não encontrada.</p>

      <Link to="/">
        Voltar para Home
      </Link>
    </section>
  );
}
