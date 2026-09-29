import { Link, Outlet } from "react-router-dom";

import "./styles.css";

export default function MainLayout() {
return ( <div className="public-layout"> <header className="public-header"> <div className="public-header__inner"> <Link
         className="public-brand"
         to="/"
         aria-label="JM Project Management — página inicial"
       > <span className="public-brand__mark" aria-hidden="true">
JM </span>

        <span className="public-brand__text">
          <strong>JM</strong>
          <span>Project Management</span>
        </span>
      </Link>

      <nav
        className="public-nav"
        aria-label="Navegação principal"
      >
        <Link to="/">Início</Link>
        <a href="/#servicos">Serviços</a>
        <a href="/#portfolio">Portefólio</a>
        <a href="/#sobre">Sobre</a>
      </nav>

      <Link
        className="public-header__action"
        to="/login"
      >
        Aceder à plataforma
        <span aria-hidden="true">→</span>
      </Link>
    </div>
  </header>

  <main className="public-main">
    <Outlet />
  </main>

  <footer className="public-footer">
    <div className="public-footer__inner">
      <span>
        © {new Date().getFullYear()} JM Project Management
      </span>

      <span>
        Desenvolvimento de soluções digitais.
      </span>
    </div>
  </footer>
</div>

);
}
