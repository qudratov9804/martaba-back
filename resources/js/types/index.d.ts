export interface User {
    id: number;
    organization_id: number | null;
    name: string;
    email: string;
    email_verified_at?: string;
    roles: string[];
    permissions: string[];
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User | null;
    };
    flash: {
        success: string | null;
        error: string | null;
    };
};
