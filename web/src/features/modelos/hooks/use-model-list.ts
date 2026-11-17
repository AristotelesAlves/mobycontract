"use client";

import { useCallback, useMemo, useState } from "react";
import { useRouter } from "next/navigation";
import type { IModeloClonar } from "../data/modelo-types";
import {
  buildModelEditorParams,
  countModelsByType,
  queryModelList,
  type ModelTypeFilter,
} from "../use-cases/query-model-list";

const PAGE_SIZE = 8;

export function useModelList(modelos: IModeloClonar[]) {
  const router = useRouter();
  const [searchTerm, setSearchTerm] = useState("");
  const [typeFilter, setTypeFilter] = useState<ModelTypeFilter>("todos");
  const [page, setPage] = useState(1);
  const [creationModalOpen, setCreationModalOpen] = useState(false);
  const [cloneModalOpen, setCloneModalOpen] = useState(false);
  const [selectedModel, setSelectedModel] = useState<IModeloClonar | null>(
    null,
  );

  const changeSearchTerm = useCallback((value: string) => {
    setSearchTerm(value);
    setPage(1);
  }, []);

  const changeTypeFilter = useCallback((value: ModelTypeFilter) => {
    setTypeFilter(value);
    setPage(1);
  }, []);

  const list = useMemo(
    () =>
      queryModelList(modelos, {
        searchTerm,
        typeFilter,
        page,
        pageSize: PAGE_SIZE,
      }),
    [modelos, searchTerm, typeFilter, page],
  );

  const counts = useMemo(() => countModelsByType(modelos), [modelos]);

  const openCloneModal = useCallback((modelo: IModeloClonar) => {
    setSelectedModel(modelo);
    setCloneModalOpen(true);
  }, []);

  const openEditor = useCallback(
    (modelo: IModeloClonar) => {
      router.push(`/modelos/novo?${buildModelEditorParams(modelo)}`);
    },
    [router],
  );

  return {
    searchTerm,
    typeFilter,
    page,
    creationModalOpen,
    cloneModalOpen,
    selectedModel,
    models: list.paged,
    counts,
    filteredCount: list.filtered.length,
    pageSize: PAGE_SIZE,
    totalPages: list.totalPages,
    openCreationModal: () => setCreationModalOpen(true),
    closeCreationModal: () => setCreationModalOpen(false),
    closeCloneModal: () => setCloneModalOpen(false),
    changeSearchTerm,
    changeTypeFilter,
    goToPage: setPage,
    openCloneModal,
    openEditor,
  };
}