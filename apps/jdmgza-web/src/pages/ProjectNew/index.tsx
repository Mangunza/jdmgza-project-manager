import { useState } from "react";
import type { FormEvent } from "react";
import { Link, useNavigate } from "react-router-dom";

import { useProjects } from "@jm/hooks";

export default function ProjectNewPage() {
  const navigate = useNavigate();
  const { create } = useProjects();

  const [categoryId, setCategoryId] = useState("");
  const [name, setName] = useState("");
  const [description, setDescription] = useState("");
  const [totalBudget, setTotalBudget] = useState("");
  const [deliveryDate, setDeliveryDate] = useState("");

  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    setError(null);

    const parsedCategoryId = Number(categoryId);

    if (!Number.isInteger(parsedCategoryId) || parsedCategoryId <= 0) {
      setError("Informe um ID de categoria válido.");
      return;
    }

    if (!name.trim()) {
      setError("Informe o nome do projeto.");
      return;
    }

    if (!totalBudget || Number(totalBudget) < 0) {
      setError("Informe um orçamento válido.");
      return;
    }

    setSubmitting(true);

    try {
      const project = await create({
        category_id: parsedCategoryId,
        name: name.trim(),
        description: description.trim() || null,
        total_budget: totalBudget,
        delivery_date: deliveryDate || null,
      });

      navigate(`/projects/${project.id}`);
    } catch (cause) {
      setError(
        cause instanceof Error
          ? cause.message
          : "Não foi possível criar o projeto.",
      );
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <section>
      <header>
        <Link to="/projects">← Voltar para projetos</Link>

        <h1>Novo projeto</h1>

        <p>
          Preencha os dados para criar um novo projeto.
        </p>
      </header>

      <form onSubmit={handleSubmit}>
        {error && (
          <p role="alert">
            {error}
          </p>
        )}

        <div>
          <label htmlFor="categoryId">
            ID da categoria
          </label>

          <input
            id="categoryId"
            name="categoryId"
            type="number"
            min="1"
            value={categoryId}
            onChange={(event) => setCategoryId(event.target.value)}
            required
          />

          <small>
            Informe o ID de uma categoria existente e ativa.
          </small>
        </div>

        <div>
          <label htmlFor="name">
            Nome do projeto
          </label>

          <input
            id="name"
            name="name"
            type="text"
            value={name}
            onChange={(event) => setName(event.target.value)}
            placeholder="Ex.: Website institucional"
            required
          />
        </div>

        <div>
          <label htmlFor="description">
            Descrição
          </label>

          <textarea
            id="description"
            name="description"
            value={description}
            onChange={(event) => setDescription(event.target.value)}
            placeholder="Descreva brevemente o projeto..."
            rows={5}
          />
        </div>

        <div>
          <label htmlFor="totalBudget">
            Orçamento total
          </label>

          <input
            id="totalBudget"
            name="totalBudget"
            type="number"
            min="0"
            step="0.01"
            value={totalBudget}
            onChange={(event) => setTotalBudget(event.target.value)}
            placeholder="0.00"
            required
          />
        </div>

        <div>
          <label htmlFor="deliveryDate">
            Data de entrega
          </label>

          <input
            id="deliveryDate"
            name="deliveryDate"
            type="date"
            value={deliveryDate}
            onChange={(event) => setDeliveryDate(event.target.value)}
          />
        </div>

        <div>
          <button
            type="submit"
            disabled={submitting}
          >
            {submitting ? "Criando..." : "Criar projeto"}
          </button>

          <Link to="/projects">
            Cancelar
          </Link>
        </div>
      </form>
    </section>
  );
}
