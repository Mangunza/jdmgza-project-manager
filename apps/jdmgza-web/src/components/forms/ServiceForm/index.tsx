import { useEffect, useState } from "react";

import {
  Alert,
  Button,
  Input,
  Textarea,
} from "@jm/ui";

import "./styles.css";

export interface ServiceFormValues {
  name: string;
  description: string;
  default_cost: string;
}

export interface ServiceFormProps {
  initialValues?: ServiceFormValues;
  loading?: boolean;
  submitLabel?: string;
  onSubmit: (values: ServiceFormValues) => Promise<void>;
  onCancel?: () => void;
}

const DEFAULT_VALUES: ServiceFormValues = {
  name: "",
  description: "",
  default_cost: "",
};

export default function ServiceForm({
  initialValues = DEFAULT_VALUES,
  loading = false,
  submitLabel = "Guardar",
  onSubmit,
  onCancel,
}: ServiceFormProps) {
  const [name, setName] = useState(initialValues.name);
  const [description, setDescription] = useState(
    initialValues.description,
  );
  const [defaultCost, setDefaultCost] = useState(
    initialValues.default_cost,
  );
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    setName(initialValues.name);
    setDescription(initialValues.description);
    setDefaultCost(initialValues.default_cost);
  }, [initialValues]);

  async function handleSubmit(
    event: React.FormEvent<HTMLFormElement>,
  ) {
    event.preventDefault();

    const trimmedName = name.trim();
    const trimmedDescription = description.trim();
    const trimmedCost = defaultCost.trim();

    if (!trimmedName) {
      setError("O nome do serviço é obrigatório.");
      return;
    }

    if (!trimmedCost) {
      setError("O custo padrão é obrigatório.");
      return;
    }

    const cost = Number(trimmedCost);

    if (!Number.isFinite(cost) || cost < 0) {
      setError("Informe um custo padrão válido.");
      return;
    }

    setError(null);

    try {
      await onSubmit({
        name: trimmedName,
        description: trimmedDescription,
        default_cost: trimmedCost,
      });
    } catch (cause) {
      setError(
        cause instanceof Error
          ? cause.message
          : "Não foi possível guardar o serviço.",
      );
    }
  }

  return (
    <form className="service-form" onSubmit={handleSubmit}>
      <div className="service-form-field">
        <label htmlFor="service-name">
          Nome
        </label>

        <Input
          id="service-name"
          type="text"
          value={name}
          onChange={(event) => setName(event.target.value)}
          disabled={loading}
          required
        />
      </div>

      <div className="service-form-field">
        <label htmlFor="service-description">
          Descrição
        </label>

        <Textarea
          id="service-description"
          value={description}
          onChange={(event) => setDescription(event.target.value)}
          disabled={loading}
          rows={5}
        />
      </div>

      <div className="service-form-field">
        <label htmlFor="service-default-cost">
          Custo padrão
        </label>

        <Input
          id="service-default-cost"
          type="number"
          min="0"
          step="0.01"
          value={defaultCost}
          onChange={(event) => setDefaultCost(event.target.value)}
          disabled={loading}
          required
        />
      </div>

      {error && (
        <Alert variant="danger">
          {error}
        </Alert>
      )}

      <div className="service-form-actions">
        {onCancel && (
          <Button
            type="button"
            variant="secondary"
            onClick={onCancel}
            disabled={loading}
          >
            Cancelar
          </Button>
        )}

        <Button
          type="submit"
          variant="primary"
          disabled={loading}
        >
          {loading ? "A guardar..." : submitLabel}
        </Button>
      </div>
    </form>
  );
}
