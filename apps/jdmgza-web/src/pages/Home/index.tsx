import { Link } from "react-router-dom";

import "./styles.css";

export default function Home() {
return ( <div className="home-page"> <section className="home-hero"> <div className="home-hero__content"> <span className="home-eyebrow">
DESENVOLVIMENTO DE SOFTWARE </span>

      <h1>
        Transformamos ideias em
        <span> soluções digitais.</span>
      </h1>

      <p className="home-hero__description">
        Ajudamos a transformar necessidades e ideias em
        soluções de software, com foco na organização,
        funcionalidade e evolução contínua dos projetos.
      </p>

      <div className="home-hero__actions">
        <a
          className="home-button home-button--primary"
          href="#portfolio"
        >
          Explorar portefólio
          <span aria-hidden="true">→</span>
        </a>

        <a
          className="home-button home-button--secondary"
          href="#contacto"
        >
          Falar connosco
        </a>
      </div>

      <div className="home-hero__note">
        <span
          className="home-hero__note-icon"
          aria-hidden="true"
        >
          ✓
        </span>

        <span>
          Soluções digitais pensadas para necessidades reais.
        </span>
      </div>
    </div>

    <div
      className="home-hero__visual"
      aria-hidden="true"
    >
      <div className="home-visual-orbit home-visual-orbit--outer" />
      <div className="home-visual-orbit home-visual-orbit--inner" />

      <div className="home-visual-panel">
        <div className="home-visual-panel__top">
          <div className="home-visual-panel__dots">
            <span />
            <span />
            <span />
          </div>

          <span className="home-visual-panel__caption">
            PROJECT WORKSPACE
          </span>
        </div>

        <div className="home-visual-panel__body">
          <div className="home-visual-panel__heading">
            <span className="home-visual-panel__symbol">
              JM
            </span>

            <div>
              <span className="home-visual-panel__line home-visual-panel__line--title" />
              <span className="home-visual-panel__line home-visual-panel__line--subtitle" />
            </div>
          </div>

          <div className="home-visual-panel__progress">
            <div className="home-visual-panel__progress-label">
              <span />
              <span />
            </div>

            <span className="home-visual-panel__progress-track">
              <span />
            </span>
          </div>

          <div className="home-visual-panel__items">
            <div>
              <span className="home-visual-panel__item-icon">✓</span>
              <span className="home-visual-panel__line" />
            </div>

            <div>
              <span className="home-visual-panel__item-icon">✓</span>
              <span className="home-visual-panel__line home-visual-panel__line--medium" />
            </div>

            <div>
              <span className="home-visual-panel__item-icon">•</span>
              <span className="home-visual-panel__line home-visual-panel__line--short" />
            </div>
          </div>
        </div>
      </div>

      <div className="home-visual-badge home-visual-badge--top">
        <span aria-hidden="true">✳</span>
        Ideias
      </div>

      <div className="home-visual-badge home-visual-badge--bottom">
        <span aria-hidden="true">↗</span>
        Soluções
      </div>
    </div>
  </section>

  <section
    className="home-services"
    id="servicos"
    aria-labelledby="home-services-title"
  >
    <div className="home-section-heading">
      <span className="home-eyebrow">
        O QUE FAZEMOS
      </span>

      <h2 id="home-services-title">
        Tecnologia ao serviço das tuas ideias.
      </h2>

      <p>
        Desenvolvemos soluções digitais orientadas para
        os objetivos e necessidades de cada projeto.
      </p>
    </div>

    <div className="home-services__grid">
      <article className="home-service-card">
        <span className="home-service-card__icon" aria-hidden="true">
          &lt;/&gt;
        </span>

        <h3>Desenvolvimento de software</h3>

        <p>
          Criação de aplicações e ferramentas digitais
          para responder a necessidades específicas.
        </p>
      </article>

      <article className="home-service-card">
        <span
          className="home-service-card__icon home-service-card__icon--blue"
          aria-hidden="true"
        >
          ◫
        </span>

        <h3>Soluções web</h3>

        <p>
          Experiências web estruturadas para facilitar
          o acesso à informação e a utilização dos serviços.
        </p>
      </article>

      <article className="home-service-card">
        <span
          className="home-service-card__icon home-service-card__icon--purple"
          aria-hidden="true"
        >
          ↗
        </span>

        <h3>Gestão de projetos</h3>

        <p>
          Organização do trabalho, acompanhamento de tarefas
          e apoio à evolução dos projetos de software.
        </p>
      </article>
    </div>
  </section>

  <section
    className="home-portfolio"
    id="portfolio"
    aria-labelledby="home-portfolio-title"
  >
    <div className="home-section-heading home-section-heading--portfolio">
      <div>
        <span className="home-eyebrow">
          O NOSSO TRABALHO
        </span>

        <h2 id="home-portfolio-title">
          Portefólio de projetos
        </h2>

        <p>
          Um espaço para conhecer as soluções que desenvolvemos
          e o trabalho realizado em cada projeto.
        </p>
      </div>
    </div>

    <div className="home-portfolio-empty">
      <span
        className="home-portfolio-empty__icon"
        aria-hidden="true"
      >
        ◫
      </span>

      <h3>Os projetos serão apresentados aqui</h3>

      <p>
        Estamos a preparar esta área para apresentar projetos,
        funcionalidades e informações sobre as soluções
        desenvolvidas pela empresa.
      </p>
    </div>
  </section>

  <section
    className="home-about"
    id="sobre"
    aria-labelledby="home-about-title"
  >
    <div className="home-about__mark" aria-hidden="true">
      JM
    </div>

    <div className="home-about__content">
      <span className="home-eyebrow">
        SOBRE A EMPRESA
      </span>

      <h2 id="home-about-title">
        Construir soluções começa por compreender o problema.
      </h2>

      <p>
        Acreditamos que o desenvolvimento de software vai
        além de escrever código. É também compreender
        necessidades, organizar ideias e criar ferramentas
        que possam evoluir com os seus utilizadores.
      </p>
    </div>
  </section>

  <section
    className="home-testimonials"
    aria-labelledby="home-testimonials-title"
  >
    <div className="home-section-heading">
      <span className="home-eyebrow">
        EXPERIÊNCIAS DE CLIENTES
      </span>

      <h2 id="home-testimonials-title">
        Testemunhos
      </h2>

      <p>
        Esta secção será atualizada com testemunhos reais
        de clientes, mediante autorização para publicação.
      </p>
    </div>

    <div className="home-testimonials__placeholder">
      Os testemunhos dos clientes serão publicados aqui
      quando estiverem disponíveis.
    </div>
  </section>

  <section
    className="home-contact"
    id="contacto"
    aria-labelledby="home-contact-title"
  >
    <div>
      <span className="home-eyebrow home-eyebrow--light">
        VAMOS CONVERSAR
      </span>

      <h2 id="home-contact-title">
        Tens uma ideia ou um projeto em mente?
      </h2>

      <p>
        Entra em contacto connosco para partilhares a tua
        ideia e conversarmos sobre as tuas necessidades.
      </p>
    </div>

    <Link
      className="home-button home-button--contact"
      to="/login"
    >
      Aceder à plataforma
      <span aria-hidden="true">→</span>
    </Link>
  </section>
</div>

);
}
