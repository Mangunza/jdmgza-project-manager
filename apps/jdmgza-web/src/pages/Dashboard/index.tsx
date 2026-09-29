import { Link } from "react-router-dom";

import "./styles.css";

export default function Dashboard() {
  return (
    <section className="dashboard-page">
      {" "}
      <header className="dashboard-page__header">
        {" "}
        <div>
          {" "}
          <p className="dashboard-page__eyebrow">ÁREA DE TRABALHO </p>
          <h1 className="dashboard-page__title">Dashboard</h1>
          <p className="dashboard-page__description">
            Acompanha e organiza o trabalho dos teus projetos num único lugar.
          </p>
        </div>
        <Link className="dashboard-page__primary-action" to="/projects/new">
          <span aria-hidden="true">+</span>
          Novo projeto
        </Link>
      </header>
      <section
        className="dashboard-welcome"
        aria-labelledby="dashboard-welcome-title"
      >
        <div className="dashboard-welcome__content">
          <span className="dashboard-welcome__label">
            JM PROJECT MANAGEMENT
          </span>

          <h2 id="dashboard-welcome-title">
            Organiza o teu trabalho.
            <br />
            Acompanha cada projeto.
          </h2>

          <p>
            Centraliza a gestão dos teus projetos e mantém a informação
            importante acessível à tua equipa.
          </p>

          <Link className="dashboard-welcome__link" to="/projects">
            Explorar projetos
            <span aria-hidden="true">→</span>
          </Link>
        </div>

        <div className="dashboard-welcome__decoration" aria-hidden="true">
          <div className="dashboard-welcome__decoration-card">
            <span className="dashboard-welcome__decoration-icon">✓</span>

            <div>
              <span className="dashboard-welcome__decoration-line dashboard-welcome__decoration-line--long" />
              <span className="dashboard-welcome__decoration-line dashboard-welcome__decoration-line--short" />
            </div>
          </div>

          <div className="dashboard-welcome__decoration-orbit" />
        </div>
      </section>
      <section
        className="dashboard-shortcuts"
        aria-labelledby="dashboard-shortcuts-title"
      >
        <div className="dashboard-section-heading">
          <div>
            <h2 id="dashboard-shortcuts-title">Acesso rápido</h2>

            <p>Atalhos para as principais áreas de trabalho.</p>
          </div>
        </div>

        <div className="dashboard-shortcuts__grid">
          <Link className="dashboard-shortcut-card" to="/projects">
            <span className="dashboard-shortcut-card__icon dashboard-shortcut-card__icon--green">
              <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <rect x="3.5" y="4.5" width="17" height="15" rx="2" />
                <path d="M8 8.5h8M8 12h8M8 15.5h4" />
              </svg>
            </span>

            <span className="dashboard-shortcut-card__body">
              <strong>Projetos</strong>
              <span>Consulta e acompanha os projetos existentes.</span>
            </span>

            <span className="dashboard-shortcut-card__arrow" aria-hidden="true">
              →
            </span>
          </Link>

          <Link className="dashboard-shortcut-card" to="/projects/new">
            <span className="dashboard-shortcut-card__icon dashboard-shortcut-card__icon--blue">
              <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path d="M12 5v14M5 12h14" />
              </svg>
            </span>

            <span className="dashboard-shortcut-card__body">
              <strong>Criar projeto</strong>
              <span>Começa um novo projeto e define os seus dados.</span>
            </span>

            <span className="dashboard-shortcut-card__arrow" aria-hidden="true">
              →
            </span>
          </Link>
        </div>
      </section>
      <section
        className="dashboard-next-step"
        aria-labelledby="dashboard-next-step-title"
      >
        <div className="dashboard-next-step__marker" />

        <div>
          <h2 id="dashboard-next-step-title">O teu espaço de gestão</h2>

          <p>
            À medida que os dados e indicadores do sistema estiverem
            disponíveis, esta área poderá apresentar um resumo atualizado da
            atividade dos projetos.
          </p>
        </div>
      </section>
    </section>
  );
}
