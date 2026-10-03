import { useState } from "react";
import { Link, useParams } from "react-router-dom";

import { useService } from "@jm/hooks";
import type { UpdateServicePayload } from "@jm/types";
import { Alert, Button } from "@jm/ui";

import ServiceForm from "../../../components/forms/ServiceForm";
import type { ServiceFormValues } from "../../../components/forms/ServiceForm";

import "./styles.css";

export default function ServiceDetails() {
  const { serviceId } = useParams<{ serviceId: string }>();
  const [isEditing, setIsEditing] = useState(false);
  const [saving, setSaving] = useState(false);
  const [changingStatus, setChangingStatus] = useState(false);

  const {
    service,
    loading,
    error,
    refresh,
    update,
    activate,
    deactivate,
  } = useService(serviceId ?? "");

  async function handleUpdate(
    values: ServiceFormValues,
  ): Promise<void> {
    const payload: UpdateServicePayload = {
      name: values.name,
      description: values.description || undefined,
      default_cost: values.default_cost,
    };

    setSaving(true);

    try {
      await update(payload);
      setIsEditing(false);
    } finally {
      setSaving(false);
    }
  }

  async function handleStatusChange(): Promise<void> {
    if (!service) {
      return;
    }

    setChangingStatus(true);

    try {
      if (service.is_active) {
        await deactivate();
      } else {
        await activate();
      }
    } finally {
      setChangingStatus(false);
    }
  }

  if (loading) {
    return (
      <section className="service-details-page">
        <div className="service-details-header">
          <div>
            <p className="service-details-eyebrow">Catálogo</p>
            <h1>Serviço</h1>
          </div>

          <Link to="/services" className="service-details-back">
            Voltar para serviços
          </Link>
        </div>

        <div className="service-details-state">
          <p>A carregar serviço...</p>
        </div>
      </section>
    );
  }

  if (error) {
    return (
      <section className="service-details-page">
        <div className="service-details-header">
          <div>
            <p className="service-details-eyebrow">Catálogo</p>
            <h1>Serviço</h1>
          </div>

          <Link to="/services" className="service-details-back">
            Voltar para serviços
          </Link>
        </div>

        <div className="service-details-state service-details-state-error">
          <Alert variant="danger">
            {error.message}
          </Alert>

          <Button
            type="button"
            variant="secondary"
            onClick={() => void refresh()}
          >
            Tentar novamente
          </Button>
        </div>
      </section>
    );
  }

  if (!service) {
    return (
      <section className="service-details-page">
        <div className="service-details-header">
          <div>
            <p className="service-details-eyebrow">Catálogo</p>
            <h1>Serviço não encontrado</h1>
          </div>

          <Link to="/services" className="service-details-back">
            Voltar para serviços
          </Link>
        </div>

        <div className="service-details-state">
          <p>Não foi possível encontrar o serviço solicitado.</p>
        </div>
      </section>
    );
  }

  if (isEditing) {
    return (
      <section className="service-details-page">
        <div className="service-details-header">
          <div>
            <p className="service-details-eyebrow">
              Catálogo / Serviço
            </p>

            <h1>Editar serviço</h1>

            <p className="service-details-description">
              Atualize as informações do serviço.
            </p>
          </div>

          <Link to="/services" className="service-details-back">
            Voltar para serviços
          </Link>
        </div>

        <div className="service-details-card">
          <ServiceForm
            initialValues={{
              name: service.name,
              description: service.description ?? "",
              default_cost: service.default_cost,
            }}
            loading={saving}
            submitLabel="Guardar alterações"
            onSubmit={handleUpdate}
            onCancel={() => setIsEditing(false)}
          />
        </div>
      </section>
    );
  }

  return (
    <section className="service-details-page">
      <div className="service-details-header">
        <div>
          <p className="service-details-eyebrow">
            Catálogo / Serviço
          </p>

          <h1>{service.name}</h1>

          <p className="service-details-description">
            {service.description || "Sem descrição disponível."}
          </p>
        </div>

        <Link to="/services" className="service-details-back">
          Voltar para serviços
        </Link>
      </div>

      <div className="service-details-card">
        <div className="service-details-card__header">
          <div>
            <span className="service-details-label">
              Estado
            </span>

            <span
              className={
                service.is_active
                  ? "service-details-status service-details-status-active"
                  : "service-details-status"
              }
            >
              {service.is_active ? "Ativo" : "Inativo"}
            </span>
          </div>

          <div>
            <span className="service-details-label">
              Custo padrão
            </span>

            <strong className="service-details-cost">
              {service.default_cost}
            </strong>
          </div>
        </div>

        <div className="service-details-meta">
          <div>
            <span className="service-details-label">
              ID
            </span>

            <span>{service.id}</span>
          </div>

          <div>
            <span className="service-details-label">
              Criado em
            </span>

            <span>{service.created_at}</span>
          </div>

          <div>
            <span className="service-details-label">
              Atualizado em
            </span>

            <span>{service.updated_at}</span>
          </div>
        </div>

        <div className="service-details-actions">
          <Button
            type="button"
            variant="primary"
            onClick={() => setIsEditing(true)}
            disabled={changingStatus}
          >
            Editar serviço
          </Button>

          <Button
            type="button"
            variant={service.is_active ? "danger" : "secondary"}
            onClick={() => void handleStatusChange()}
            disabled={changingStatus}
          >
            {changingStatus
              ? "A atualizar..."
              : service.is_active
                ? "Desativar serviço"
                : "Ativar serviço"}
          </Button>
        </div>
      </div>
    </section>
  );
}
