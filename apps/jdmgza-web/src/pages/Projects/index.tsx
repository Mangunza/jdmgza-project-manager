import { Link } from "react-router-dom";

import { useProjects } from "@jm/hooks";

export default function ProjectsPage() {
  const {
    projects,
    loading,
    error,
    page,
    lastPage,
    total,
    setPage,
    refresh,
  } = useProjects();

  if (loading) {
    return (
      <section>
        <h1>Projects</h1>
        <p>Carregando projetos...</p>
      </section>
    );
  }

  if (error) {
    return (
      <section>
        <h1>Projects</h1>
        <p role="alert">{error.message}</p>

        <button type="button" onClick={refresh}>
          Tentar novamente
        </button>
      </section>
    );
  }

  return (
    <section>
      <header>
        <div>
          <h1>Projects</h1>
          <p>
            {total} projeto{total === 1 ? "" : "s"} encontrado
            {total === 1 ? "" : "s"}.
          </p>
        </div>

        <Link to="/projects/new">Novo projeto</Link>
      </header>

      {projects.length === 0 ? (
        <div>
          <h2>Nenhum projeto encontrado</h2>
          <p>Você ainda não possui projetos cadastrados.</p>
          <Link to="/projects/new">Criar primeiro projeto</Link>
        </div>
      ) : (
        <div>
          {projects.map((project) => (
            <article key={project.id}>
              <h2>
                <Link to={`/projects/${project.id}`}>{project.name}</Link>
              </h2>

              {project.description && <p>{project.description}</p>}

              <dl>
                <div>
                  <dt>Categoria</dt>
                  <dd>{project.category?.name ?? "Sem categoria"}</dd>
                </div>

                <div>
                  <dt>Orçamento</dt>
                  <dd>{project.total_budget}</dd>
                </div>

                <div>
                  <dt>Custo</dt>
                  <dd>{project.total_cost}</dd>
                </div>

                <div>
                  <dt>Status</dt>
                  <dd>{project.status ?? "Sem status"}</dd>
                </div>

                <div>
                  <dt>Entrega</dt>
                  <dd>{project.delivery_date ?? "Não definida"}</dd>
                </div>
              </dl>
            </article>
          ))}
        </div>
      )}

      {lastPage > 1 && (
        <nav aria-label="Paginação de projetos">
          <button
            type="button"
            disabled={page <= 1}
            onClick={() => setPage(page - 1)}
          >
            Anterior
          </button>

          <span>
            Página {page} de {lastPage}
          </span>

          <button
            type="button"
            disabled={page >= lastPage}
            onClick={() => setPage(page + 1)}
          >
            Próxima
          </button>
        </nav>
      )}
    </section>
  );
}
