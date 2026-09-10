import { Link, useParams } from "react-router-dom";

import { useProject } from "@jm/hooks";

export default function ProjectDetailPage() {
  const { projectId } = useParams();
  const parsedProjectId = Number(projectId);

  const {
    project,
    loading,
    error,
    refresh,
  } = useProject(parsedProjectId);

  if (!Number.isInteger(parsedProjectId) || parsedProjectId <= 0) {
    return (
      <section>
        <h1>Projeto inválido</h1>
        <p>O identificador do projeto não é válido.</p>
        <Link to="/projects">Voltar para projetos</Link>
      </section>
    );
  }

  if (loading) {
    return (
      <section>
        <Link to="/projects">← Voltar para projetos</Link>
        <h1>Projeto</h1>
        <p>Carregando projeto...</p>
      </section>
    );
  }

  if (error) {
    return (
      <section>
        <Link to="/projects">← Voltar para projetos</Link>
        <h1>Não foi possível carregar o projeto</h1>
        <p role="alert">{error.message}</p>

        <button type="button" onClick={refresh}>
          Tentar novamente
        </button>
      </section>
    );
  }

  if (!project) {
    return (
      <section>
        <Link to="/projects">← Voltar para projetos</Link>
        <h1>Projeto não encontrado</h1>
        <p>Não foi possível encontrar os dados deste projeto.</p>
      </section>
    );
  }

  return (
    <section>
      <header>
        <div>
          <Link to="/projects">← Voltar para projetos</Link>
          <h1>{project.name}</h1>
          <p>{project.description ?? "Sem descrição."}</p>
        </div>

      </header>

      <dl>
        <div>
          <dt>Categoria</dt>
          <dd>{project.category?.name ?? "Sem categoria"}</dd>
        </div>

        <div>
          <dt>Orçamento total</dt>
          <dd>{project.total_budget}</dd>
        </div>

        <div>
          <dt>Custo total</dt>
          <dd>{project.total_cost}</dd>
        </div>

        <div>
          <dt>Status</dt>
          <dd>{project.status ?? "Sem status"}</dd>
        </div>

        <div>
          <dt>Data de entrega</dt>
          <dd>{project.delivery_date ?? "Não definida"}</dd>
        </div>

        <div>
          <dt>Criado em</dt>
          <dd>{project.created_at ?? "Não disponível"}</dd>
        </div>

        <div>
          <dt>Atualizado em</dt>
          <dd>{project.updated_at ?? "Não disponível"}</dd>
        </div>
      </dl>
    </section>
  );
}
