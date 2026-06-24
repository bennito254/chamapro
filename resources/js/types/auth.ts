export type SuperAdmin = {
    id: number;
    name: string;
    email: string;
    last_login?: string | null;
};

export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    group_id?: number | null;
    created_at: string;
    updated_at: string;
};

export type Auth = {
    user: User | null;
    superAdmin?: SuperAdmin | null;
};

export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};

export type TwoFactorSetupData = {
    svg: string;
    url: string;
};

export type TwoFactorSecretKey = {
    secretKey: string;
};
