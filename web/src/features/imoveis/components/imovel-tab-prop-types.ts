import type { Dispatch, SetStateAction } from "react";
import type { useRouter } from "next/navigation";

export type PropertyTabRouter = ReturnType<typeof useRouter>;

export type SearchFieldSetter = Dispatch<SetStateAction<string>>;

export type PageNumberSetter = Dispatch<SetStateAction<number>>;

export type StatusFilterSetter = Dispatch<SetStateAction<string>>;

export type DrawerToggleSetter = Dispatch<SetStateAction<boolean>>;
