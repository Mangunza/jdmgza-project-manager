import { useMemo, useState } from "react";
import type { FormEvent } from "react";
import { Link, useParams } from "react-router-dom";

import { Alert, Button, Input, Select } from "@jm/ui";

import {
  useProject,
  useProjectServices,
  useServices,
} from "@jm/hooks";

export default function ProjectDetailPage() {
  const { projectId } = useParams();
  const parsedProjectId = Number(projectId);

  const {
    project,
    loading,
    error,
    refresh,
  } = useProject(parsedProjectId);

  const {
    services: projectServices,
    loading: projectServicesLoading,
    error: projectServicesError,
    add,
    update,
    remove,
  } = useProjectServices(parsedProjectId);

  const {
    services,
    loading: servicesLoading,
    error: servicesError,
  } = useServices();

  const [selectedServiceId, setSelectedServiceId] = useState("");
  const [quantity, setQuantity] = useState("1");

  const [editingServiceId, setEditingServiceId] = useState<number | null>(
    null,
  );
  const [editingQuantity, setEditingQuantity] = useState("");

  const [submitting, setSubmitting] = useState(false);
  const [actionError, setActionError] = useState<string | null>(null);

  const availableServices = useMemo(() => {
    const associatedServiceIds = new Set(
      projectServices.map((projectService) => projectService.service_id),
    );

    return services.filter(
      (service) => !associatedServiceIds.has(service.id),
    );
  }, [services, projectServices]);

  const selectedService = useMemo(
    () =>
      services.find((service) => service.id === selectedServiceId) ?? null,
    [services, selectedServiceId],
  );

  if (!Number.isInteger(parsedProjectId) || parsedProjectId <= 0) {
    return (
      <section>
        <h1>Projeto inválido</h1>

        <p>
          O identificador do projeto não é válido.
        </p>

        <Link to="/projects">
          Voltar para projetos
        </Link>
      </section>
    );
  }

  if (loading) {
    return (
      <section>
        <Link to="/projects">
          ← Voltar para projetos
        </Link>

        <h1>Projeto</h1>

        <p>
          Carregando projeto...
        </p>
      </section>
    );
  }

  if (error) {
    return (
      <section>
        <Link to="/projects">
          ← Voltar para projetos
        </Link>

        <h1>
          Não foi possível carregar o projeto
        </h1>

        <Alert>
          {error.message}
        </Alert>

        <Button
          type="button"
          onClick={() => void refresh()}
        >
          Tentar novamente
        </Button>
      </section>
    );
  }

  if (!project) {
    return (
      <section>
        <Link to="/projects">
          ← Voltar para projetos
        </Link>

        <h1>
          Projeto não encontrado
        </h1>

        <p>
          Não foi possível encontrar os dados deste projeto.
        </p>
      </section>
    );
  }

  async function handleAddService(
    event: FormEvent<HTMLFormElement>,
  ) {
    event.preventDefault();

    setActionError(null);

    if (!selectedServiceId) {
      setActionError("Selecione um serviço.");
      return;
    }

    const parsedQuantity = Number(quantity);

    if (
      !Number.isFinite(parsedQuantity) ||
      parsedQuantity <= 0
    ) {
      setActionError(
        "Informe uma quantidade maior que zero.",
      );
      return;
    }

    setSubmitting(true);

    try {
      await add({
        service_id: selectedServiceId,
        quantity: parsedQuantity,
      });

      setSelectedServiceId("");
      setQuantity("1");

      await refresh();
    } catch (cause) {
      setActionError(
        cause instanceof Error
          ? cause.message
          : "Não foi possível adicionar o serviço.",
      );
    } finally {
      setSubmitting(false);
    }
  }

  function startEditingQuantity(
    projectServiceId: number,
    currentQuantity: string,
  ) {
    setActionError(null);
    setEditingServiceId(projectServiceId);
    setEditingQuantity(currentQuantity);
  }

  function cancelEditingQuantity() {
    setEditingServiceId(null);
    setEditingQuantity("");
  }

  async function handleUpdateQuantity(
    projectServiceId: number,
  ) {
    setActionError(null);

    const parsedQuantity = Number(editingQuantity);

    if (
      !Number.isFinite(parsedQuantity) ||
      parsedQuantity <= 0
    ) {
      setActionError(
        "Informe uma quantidade maior que zero.",
      );
      return;
    }

    setSubmitting(true);

    try {
      await update(projectServiceId, {
        quantity: parsedQuantity,
      });

      cancelEditingQuantity();

      await refresh();
    } catch (cause) {
      setActionError(
        cause instanceof Error
          ? cause.message
          : "Não foi possível atualizar a quantidade.",
      );
    } finally {
      setSubmitting(false);
    }
  }

  async function handleRemoveService(
    projectServiceId: number,
  ) {
    setActionError(null);

    const confirmed = window.confirm(
      "Tem certeza que deseja remover este serviço do projeto?",
    );

    if (!confirmed) {
      return;
    }

    setSubmitting(true);

    try {
      await remove(projectServiceId);

      if (editingServiceId === projectServiceId) {
        cancelEditingQuantity();
      }

      await refresh();
    } catch (cause) {
      setActionError(
        cause instanceof Error
          ? cause.message
          : "Não foi possível remover o serviço.",
      );
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <section>
      <header>
        <div>
          <Link to="/projects">
            ← Voltar para projetos
          </Link>

          <h1>{project.name}</h1>

          <p>
            {project.description ?? "Sem descrição."}
          </p>
        </div>
      </header>

      <dl>
        <div>
          <dt>Categoria</dt>
          <dd>
            {project.category?.name ?? "Sem categoria"}
          </dd>
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
          <dd>
            {project.status ?? "Sem status"}
          </dd>
        </div>

        <div>
          <dt>Data de entrega</dt>
          <dd>
            {project.delivery_date ?? "Não definida"}
          </dd>
        </div>

        <div>
          <dt>Criado em</dt>
          <dd>
            {project.created_at ?? "Não disponível"}
          </dd>
        </div>

        <div>
          <dt>Atualizado em</dt>
          <dd>
            {project.updated_at ?? "Não disponível"}
          </dd>
        </div>
      </dl>

      <section>
        <h2>Serviços do projeto</h2>

        <form onSubmit={handleAddService}>
          <div>
            <label htmlFor="serviceId">
              Serviço
            </label>

            <Select
              id="serviceId"
              name="serviceId"
              value={selectedServiceId}
              onChange={(event) =>
                setSelectedServiceId(event.target.value)
              }
              disabled={
                servicesLoading ||
                submitting ||
                availableServices.length === 0
              }
              required
            >
              <option value="">
                {servicesLoading
                  ? "Carregando serviços..."
                  : availableServices.length === 0
                    ? "Nenhum serviço disponível"
                    : "Selecione um serviço"}
              </option>

              {availableServices.map((service) => (
                <option
                  key={service.id}
                  value={service.id}
                >
                  {service.name} — {service.default_cost}
                </option>
              ))}
            </Select>
          </div>

          {selectedService && (
            <div>
              <strong>
                {selectedService.name}
              </strong>

              {selectedService.description && (
                <p>
                  {selectedService.description}
                </p>
              )}

              <p>
                Custo padrão:{" "}
                {selectedService.default_cost}
              </p>
            </div>
          )}

          <div>
            <label htmlFor="quantity">
              Quantidade
            </label>

            <Input
              id="quantity"
              name="quantity"
              type="number"
              min="0.01"
              step="0.01"
              value={quantity}
              onChange={(event) =>
                setQuantity(event.target.value)
              }
              disabled={submitting}
              required
            />
          </div>

          <Button
            type="submit"
            disabled={
              submitting ||
              servicesLoading ||
              availableServices.length === 0
            }
          >
            {submitting
              ? "Processando..."
              : "Adicionar serviço"}
          </Button>
        </form>

        {servicesError && (
          <Alert>
            Não foi possível carregar os serviços:{" "}
            {servicesError.message}
          </Alert>
        )}

        {projectServicesError && (
          <Alert>
            Não foi possível carregar os serviços do projeto:{" "}
            {projectServicesError.message}
          </Alert>
        )}

        {actionError && (
          <Alert>
            {actionError}
          </Alert>
        )}

        {projectServicesLoading ? (
          <p>
            Carregando serviços do projeto...
          </p>
        ) : projectServices.length === 0 ? (
          <div>
            <h3>
              Nenhum serviço associado
            </h3>

            <p>
              Adicione um serviço para começar a
              calcular o custo do projeto.
            </p>
          </div>
        ) : (
          <div>
            {projectServices.map((projectService) => {
              const isEditing =
                editingServiceId === projectService.id;

              return (
                <article
                  key={projectService.id}
                >
                  <h3>
                    {projectService.name}
                  </h3>

                  {projectService.description && (
                    <p>
                      {projectService.description}
                    </p>
                  )}

                  <dl>
                    <div>
                      <dt>Quantidade</dt>
                      <dd>
                        {isEditing ? (
                          <Input
                            type="number"
                            min="0.01"
                            step="0.01"
                            value={editingQuantity}
                            onChange={(event) =>
                              setEditingQuantity(
                                event.target.value,
                              )
                            }
                            disabled={submitting}
                            aria-label={`Nova quantidade para ${projectService.name}`}
                          />
                        ) : (
                          projectService.quantity
                        )}
                      </dd>
                    </div>

                    <div>
                      <dt>Custo unitário</dt>
                      <dd>
                        {projectService.unit_cost}
                      </dd>
                    </div>

                    <div>
                      <dt>Custo total</dt>
                      <dd>
                        {projectService.total_cost}
                      </dd>
                    </div>
                  </dl>

                  {isEditing ? (
                    <>
                      <Button
                        type="button"
                        disabled={submitting}
                        onClick={() =>
                          void handleUpdateQuantity(
                            projectService.id,
                          )
                        }
                      >
                        {submitting
                          ? "Guardando..."
                          : "Guardar"}
                      </Button>

                      <Button
                        type="button"
                        disabled={submitting}
                        onClick={cancelEditingQuantity}
                      >
                        Cancelar
                      </Button>
                    </>
                  ) : (
                    <Button
                      type="button"
                      disabled={submitting}
                      onClick={() =>
                        startEditingQuantity(
                          projectService.id,
                          projectService.quantity,
                        )
                      }
                    >
                      Alterar quantidade
                    </Button>
                  )}

                  <Button
                    type="button"
                    disabled={submitting}
                    onClick={() =>
                      void handleRemoveService(
                        projectService.id,
                      )
                    }
                  >
                    Remover
                  </Button>
                </article>
              );
            })}
          </div>
        )}
      </section>
    </section>
  );
}
