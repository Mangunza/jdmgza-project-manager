import { useState } from "react";
import { Link, useNavigate } from "react-router-dom";

import { useServices } from "@jm/hooks";
import type { CreateServicePayload } from "@jm/types";
import { Button } from "@jm/ui";

import ServiceForm from "../../../components/forms/ServiceForm";
import type { ServiceFormValues } from "../../../components/forms/ServiceForm";

import "./styles.css";

export default function ServiceNew() {
  const navigate = useNavigate();
  const { create } = useServices();
  const [saving, setSaving] = useState(false);

  async function handleCreate(
    values: ServiceFormValues,
  ): Promise<void> {
    const payload: CreateServicePayload = {
      name: values.name,
      description: values.description || undefined,
      default_cost: values.default_cost,
    };

    setSaving(true);

    try {
      const service = await create(payload);

      navigate(`/services/${service.id}`);
    } finally {
      setSaving(false);
    }
  }

  return (
    <section className="service-new-page">
      <div className="service-new-header">
        <div>
          <p className="service-new-eyebrow">
            Catálogo / Serviços
          </p>

          <h1>Novo serviço</h1>

          <p className="service-new-description">
            Adicione um novo serviço ao catálogo.
          </p>
        </div>

        <Link to="/services">
          <Button
            type="button"
            variant="secondary"
          >
            Voltar para serviços
          </Button>
        </Link>
      </div>

      <div className="service-new-card">
        <ServiceForm
          loading={saving}
          submitLabel="Criar serviço"
          onSubmit={handleCreate}
          onCancel={() => navigate("/services")}
        />
      </div>
    </section>
  );
}
