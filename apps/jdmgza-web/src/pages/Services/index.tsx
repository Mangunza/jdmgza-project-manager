import { useMemo, useState } from "react";
import { Link } from "react-router-dom";
import { useAuth } from "@jm/auth";

import { useServices } from "@jm/hooks";
import { Button, ErrorState, LoadingState } from "@jm/ui";

import "./styles.css";

type ServiceStatusFilter = "all" | "active" | "inactive";

export default function Services() {
  const { hasPermission } = useAuth();

  const canViewInactive = hasPermission("services.view_inactive");

  const { services, loading, error, refresh } = useServices(canViewInactive);

  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState<ServiceStatusFilter>("all");

  const activeCount = useMemo(
    () => services.filter((service) => service.is_active).length,
    [services],
  );

  const inactiveCount = services.length - activeCount;

  const filteredServices = useMemo(() => {
    const normalizedSearch = search.trim().toLowerCase();

    return services.filter((service) => {
      const matchesStatus =
        statusFilter === "all" ||
        (statusFilter === "active" && service.is_active) ||
        (statusFilter === "inactive" && !service.is_active);

      if (!matchesStatus) {
        return false;
      }

      if (!normalizedSearch) {
        return true;
      }

      const name = service.name.toLowerCase();
      const description = service.description?.toLowerCase() ?? "";

      return (
        name.includes(normalizedSearch) ||
        description.includes(normalizedSearch)
      );
    });
  }, [search, services, statusFilter]);

  if (loading) {
    return (
      <section className="services-page">
        <div className="services-header">
          <div>
            <p className="services-eyebrow">Catálogo</p>
            <h1>Serviços</h1>
            <p className="services-description">
              Gerencie os serviços disponíveis para utilização nos projetos.
            </p>
          </div>
        </div>

        <LoadingState message="A carregar serviços..." />
      </section>
    );
  }

  if (error) {
    return (
      <section className="services-page">
        <div className="services-header">
          <div>
            <p className="services-eyebrow">Catálogo</p>
            <h1>Serviços</h1>
            <p className="services-description">
              Gerencie os serviços disponíveis para utilização nos projetos.
            </p>
          </div>
        </div>

        <div className="services-state services-state-error">
          <ErrorState message={error.message} onRetry={refresh} />
        </div>
      </section>
    );
  }

  return (
    <section className="services-page">
      <div className="services-header">
        <div>
          <p className="services-eyebrow">Catálogo</p>

          <h1>Serviços</h1>

          <p className="services-description">
            Gerencie os serviços disponíveis para utilização nos projetos.
          </p>
        </div>

        <div className="services-header-actions">
          <span className="services-count">
            {filteredServices.length}{" "}
            {filteredServices.length === 1 ? "serviço" : "serviços"}
          </span>

          {hasPermission("services.create") && (
            <Link to="/services/new">
              <Button type="button" variant="primary">
                Novo serviço
              </Button>
            </Link>
          )}
        </div>
      </div>

      <div className="services-summary">
        <button
          type="button"
          className={
            statusFilter === "all"
              ? "services-summary-card services-summary-card-active"
              : "services-summary-card"
          }
          onClick={() => setStatusFilter("all")}
        >
          <span className="services-summary-label">Total</span>

          <strong className="services-summary-value">{services.length}</strong>

          <span className="services-summary-description">
            Todos os serviços
          </span>
        </button>

        <button
          type="button"
          className={
            statusFilter === "active"
              ? "services-summary-card services-summary-card-active"
              : "services-summary-card"
          }
          onClick={() => setStatusFilter("active")}
        >
          <span className="services-summary-label">Ativos</span>

          <strong className="services-summary-value">{activeCount}</strong>

          <span className="services-summary-description">
            Disponíveis para utilização
          </span>
        </button>

        <button
          type="button"
          className={
            statusFilter === "inactive"
              ? "services-summary-card services-summary-card-active"
              : "services-summary-card"
          }
          onClick={() => setStatusFilter("inactive")}
        >
          <span className="services-summary-label">Inativos</span>

          <strong className="services-summary-value">{inactiveCount}</strong>

          <span className="services-summary-description">
            Temporariamente desativados
          </span>
        </button>
      </div>

      <div className="services-toolbar">
        <div className="services-search">
          <label htmlFor="services-search" className="services-search-label">
            Pesquisar serviços
          </label>

          <input
            id="services-search"
            className="services-search-input"
            type="search"
            value={search}
            onChange={(event) => setSearch(event.target.value)}
            placeholder="Pesquisar por nome ou descrição..."
          />
        </div>

        <div className="services-filter">
          <label
            htmlFor="services-status-filter"
            className="services-filter-label"
          >
            Estado
          </label>

          <select
            id="services-status-filter"
            className="services-filter-select"
            value={statusFilter}
            onChange={(event) =>
              setStatusFilter(event.target.value as ServiceStatusFilter)
            }
          >
            <option value="all">Todos</option>
            <option value="active">Ativos</option>
            <option value="inactive">Inativos</option>
          </select>
        </div>
      </div>

      {filteredServices.length === 0 ? (
        <div className="services-state">
          <p className="services-state-title">Nenhum serviço encontrado.</p>

          <p className="services-state-description">
            {services.length === 0
              ? "Ainda não existem serviços cadastrados."
              : "Tente alterar a pesquisa ou o filtro de estado."}
          </p>
        </div>
      ) : (
        <div className="services-grid">
          {filteredServices.map((service) => (
            <article
              className={
                service.is_active
                  ? "service-card"
                  : "service-card service-card-inactive"
              }
              key={service.id}
            >
              <div className="service-card-header">
                <div className="service-card-heading">
                  <span
                    className={
                      service.is_active
                        ? "service-status service-status-active"
                        : "service-status service-status-inactive"
                    }
                  >
                    <span className="service-status-dot" aria-hidden="true" />

                    {service.is_active ? "Ativo" : "Inativo"}
                  </span>

                  <h2>{service.name}</h2>
                </div>

                <strong className="service-card-cost">
                  {service.default_cost}
                </strong>
              </div>

              <p className="service-card-description">
                {service.description || "Sem descrição disponível."}
              </p>

              <div className="service-card-footer">
                <span className="service-card-meta">Serviço do catálogo</span>

                <Link
                  className="service-card-link"
                  to={`/services/${service.id}`}
                >
                  Ver detalhes
                </Link>
              </div>
            </article>
          ))}
        </div>
      )}
    </section>
  );
}
