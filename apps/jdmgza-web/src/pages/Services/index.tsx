import { Link } from "react-router-dom";

import { useServices } from "@jm/hooks";
import {
  Button,
  ErrorState,
  LoadingState,
} from "@jm/ui";

import "./styles.css";

export default function Services() {
  const {
    services,
    loading,
    error,
    refresh,
  } = useServices();

  if (loading) {
    return (
      <section className="services-page">
        <div className="services-header">
          <div>
            <p className="services-eyebrow">Catálogo</p>
            <h1>Serviços</h1>
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
          </div>
        </div>

        <div className="services-state services-state-error">
          <ErrorState
            message={error.message}
            onRetry={refresh}
          />
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
            Serviços disponíveis para utilização nos projetos.
          </p>
        </div>

        <div className="services-header-actions">
          <span className="services-count">
            {services.length}{" "}
            {services.length === 1 ? "serviço" : "serviços"}
          </span>

          <Link to="/services/new">
            <Button type="button" variant="primary">
              Novo serviço
            </Button>
          </Link>
        </div>
      </div>

      {services.length === 0 ? (
        <div className="services-state">
          <p>Nenhum serviço encontrado.</p>
        </div>
      ) : (
        <div className="services-grid">
          {services.map((service) => (
            <Link
              className="service-card"
              key={service.id}
              to={`/services/${service.id}`}
            >
              <div className="service-card-header">
                <div>
                  <h2>{service.name}</h2>

                  <span
                    className={
                      service.is_active
                        ? "service-status service-status-active"
                        : "service-status"
                    }
                  >
                    {service.is_active ? "Ativo" : "Inativo"}
                  </span>
                </div>

                <strong>{service.default_cost}</strong>
              </div>

              <p className="service-card-description">
                {service.description ||
                  "Sem descrição disponível."}
              </p>
            </Link>
          ))}
        </div>
      )}
    </section>
  );
}
