"use client";

import { useCallback, useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import { useFeedback } from "@/components/ui/feedback-provider";
import type { Imovel } from "../data/imovel-types";
import {
  createProperty,
  fetchAddressByPostalCode,
  getProperty,
  updateProperty,
  uploadPropertyPhoto,
} from "../services/property-service";
import {
  buildPropertyPayload,
  propertyErrorsFromApi,
  EMPTY_PROPERTY_FORM,
  validatePropertyForm,
  type PropertyFieldErrors,
  type PropertyFieldName,
  type PropertyFormValues,
  type PropertyInclusionFlags,
} from "../use-cases/build-property-from-form";

function valoresDeImovel(imovel: Imovel): PropertyFormValues {
  return {
    titulo: imovel.titulo ?? "",
    nomeProprietario: imovel.nomeProprietario ?? "",
    tipo: imovel.tipo,
    cep: imovel.endereco.cep ?? "",
    logradouro: imovel.endereco.logradouro ?? "",
    numero: imovel.endereco.numero ?? "",
    complemento: imovel.endereco.complemento ?? "",
    bairro: imovel.endereco.bairro ?? "",
    cidade: imovel.endereco.cidade ?? "",
    estado: imovel.endereco.estado ?? "",
    area: imovel.area ? String(imovel.area) : "",
    quartos: imovel.quartos ? String(imovel.quartos) : "0",
    banheiros: imovel.banheiros ? String(imovel.banheiros) : "0",
    vagasGaragem: imovel.vagas ? String(imovel.vagas) : "0",
    valorAluguel: imovel.valorAluguel ? String(imovel.valorAluguel) : "",
    valorCondominio: imovel.valorCondominio ? String(imovel.valorCondominio) : "",
    valorIptu: imovel.valorIptu ? String(imovel.valorIptu) : "",
    matricula: imovel.matricula ?? "",
    descricao: imovel.descricao ?? "",
  };
}

function comodidadesDeImovel(imovel: Imovel): PropertyInclusionFlags {
  return {
    aguaInclusa: imovel.caracteristicas.includes("agua"),
    energiaInclusa: imovel.caracteristicas.includes("energia"),
    gasIncluso: imovel.caracteristicas.includes("gas"),
    internetInclusa: imovel.caracteristicas.includes("internet"),
  };
}

export function usePropertyRegistration(propertyId?: string) {
  const router = useRouter();
  const { pushFeedback } = useFeedback();

  const modoEdicao = Boolean(propertyId);

  const [values, setValues] = useState<PropertyFormValues>(EMPTY_PROPERTY_FORM);
  const [fieldErrors, setFieldErrors] = useState<PropertyFieldErrors>({});
  const [submitError, setSubmitError] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const [loading, setLoading] = useState(false);
  const [loadError, setLoadError] = useState("");
  const [searchingAddress, setSearchingAddress] = useState(false);
  const [waterIncluded, setWaterIncluded] = useState(false);
  const [powerIncluded, setPowerIncluded] = useState(false);
  const [gasIncluded, setGasIncluded] = useState(false);
  const [internetIncluded, setInternetIncluded] = useState(false);
  const [savedId, setSavedId] = useState<string | undefined>(propertyId);
  const [photos, setPhotos] = useState<File[]>([]);
  const [photoUploadStatus, setPhotoUploadStatus] = useState<
    "idle" | "uploading" | "done"
  >("idle");
  const [reloadKey, setReloadKey] = useState(0);

  const retry = useCallback(() => {
    setReloadKey((current) => current + 1);
  }, []);

  useEffect(() => {
    if (!propertyId) return;
    let cancelado = false;

    async function carregar() {
      setLoading(true);
      setLoadError("");
      try {
        const imovel = await getProperty(propertyId as string);
        if (cancelado) return;
        setValues(valoresDeImovel(imovel));
        const comodidades = comodidadesDeImovel(imovel);
        setWaterIncluded(comodidades.aguaInclusa);
        setPowerIncluded(comodidades.energiaInclusa);
        setGasIncluded(comodidades.gasIncluso);
        setInternetIncluded(comodidades.internetInclusa);
      } catch (erro) {
        if (cancelado) return;
        setLoadError(getReadableErrorMessage(erro));
      } finally {
        if (!cancelado) setLoading(false);
      }
    }

    carregar();
    return () => {
      cancelado = true;
    };
  }, [propertyId, reloadKey]);

  const patchField = useCallback(
    (field: PropertyFieldName, value: string) => {
      setValues((current) => ({ ...current, [field]: value }));
      setFieldErrors((current) => ({ ...current, [field]: undefined }));
    },
    [],
  );

  const searchPostalCode = useCallback(
    async (formattedPostalCode: string) => {
      const digits = formattedPostalCode.replace(/\D/g, "");
      if (digits.length !== 8) return;
      setSearchingAddress(true);
      try {
        const address = await fetchAddressByPostalCode(digits);
        if (!address) {
          setFieldErrors((current) => ({
            ...current,
            cep: "CEP não encontrado",
          }));
          return;
        }
        setValues((current) => ({
          ...current,
          logradouro: address.street || current.logradouro,
          bairro: address.district || current.bairro,
          cidade: address.city || current.cidade,
          estado: address.state || current.estado,
        }));
        setFieldErrors((current) => ({ ...current, cep: undefined }));
      } catch (error) {
        setFieldErrors((current) => ({
          ...current,
          cep: getReadableErrorMessage(error),
        }));
      } finally {
        setSearchingAddress(false);
      }
    },
    [],
  );

  const addPhotos = useCallback((incoming: File[]) => {
    if (incoming.length === 0) return;
    if (incoming.some(file => !["image/jpeg", "image/png", "image/webp"].includes(file.type) || file.size > 5 * 1024 * 1024)) {
      setSubmitError("Selecione imagens JPG, PNG ou WebP de até 5 MB.");
      return;
    }
    setPhotos(current => [...current, ...incoming]);
  }, []);

  const removePhoto = useCallback((index: number) => {
    setPhotos((current) => current.filter((_, position) => position !== index));
  }, []);

  const submit = useCallback(async () => {
    try {
      validatePropertyForm(values);
      setFieldErrors({});
      setSubmitError("");
    } catch (error) {
      setFieldErrors(
        (error as { details?: PropertyFieldErrors }).details ?? {},
      );
      setSubmitError(getReadableErrorMessage(error));
      return;
    }

    setSubmitting(true);
    try {
      const payload = buildPropertyPayload(values, {
        aguaInclusa: waterIncluded,
        energiaInclusa: powerIncluded,
        gasIncluso: gasIncluded,
        internetInclusa: internetIncluded,
      });

      const salvo = savedId
        ? await updateProperty(savedId, payload)
        : await createProperty(payload);

      setSavedId(salvo.id);
      setPhotoUploadStatus("uploading");
      for (const foto of photos) {
        await uploadPropertyPhoto(salvo.id, foto);
        setPhotos(current => current.filter(candidate => candidate !== foto));
      }
      setPhotoUploadStatus("idle");
      pushFeedback(
        "success",
        modoEdicao
          ? `Imóvel ${salvo.titulo} atualizado com sucesso.`
          : `Imóvel ${salvo.titulo} cadastrado com sucesso.`,
      );

      if (!propertyId) {
        setValues(EMPTY_PROPERTY_FORM);
        setPhotos([]);
      }
      router.push(`/imoveis/${salvo.id}`);
    } catch (error) {
      setFieldErrors(propertyErrorsFromApi(error));
      setSubmitError(getReadableErrorMessage(error));
      pushFeedback("error", getReadableErrorMessage(error));
      requestAnimationFrame(() => document.querySelector('[role="alert"]')?.scrollIntoView({ behavior: "smooth", block: "center" }));
    } finally {
      setPhotoUploadStatus("idle");
      setSubmitting(false);
    }
  }, [
    values,
    waterIncluded,
    powerIncluded,
    gasIncluded,
    internetIncluded,
    propertyId,
    savedId,
    photos,
    modoEdicao,
    pushFeedback,
    router,
  ]);

  return {
    modoEdicao,
    values,
    fieldErrors,
    submitError,
    submitting,
    loading,
    loadError,
    retry,
    searchingAddress,
    waterIncluded,
    powerIncluded,
    gasIncluded,
    internetIncluded,
    photos,
    photoUploadStatus,
    patchField,
    searchPostalCode,
    addPhotos,
    removePhoto,
    setWaterIncluded,
    setPowerIncluded,
    setGasIncluded,
    setInternetIncluded,
    submit,
  };
}
