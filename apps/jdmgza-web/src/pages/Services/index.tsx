import { useEffect, useState } from "react";

import { getServices } from "@jm/api";
import type { Service } from "@jm/types";

import "./styles.css";

export default function Services() {
  const [services, setServices] = useState<Service[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let mounted = true;

    async function loadServices() {
      try {
        setLoading(true);
        setError(null);

        const data = await getServices();

        if (mounted) {
          setServices(data);
        }
      } catch {
        if (mounted) {
          setError("Não foi possível carregar os serviços.");
        }
      } finally {
        if (mounted) {
          setLoading(false);
        }
      }
    }

    void loadServices();

    return () => {
      mounted = false;
    };
  }, []);

  if (loading) {
    return (
      <section className="services-page">
        <div className="services-header">
          <div>
            <p className="services-eyebrow">Catálogo</p>
            <h1>Serviços</h1>
          </div>
        </div>

        <p className="services-state">A carregar serviços...</p>
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
          <p>{error}</p>
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

        <span className="services-count">
          {services.length} {services.length === 1 ? "serviço" : "serviços"}
        </span>
      </div>

      {services.length === 0 ? (
        <div className="services-state">
          <p>Nenhum serviço encontrado.</p>
        </div>
      ) : (
        <div className="services-grid">
          {services.map((service) => (
            <article className="service-card" key={service.id}>
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
                {service.description || "Sem descrição disponível."}
              </p>
            </article>
          ))}
        </div>
      )}
    </section>
  );
}
