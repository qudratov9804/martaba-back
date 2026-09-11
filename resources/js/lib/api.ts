import axios, { AxiosError } from 'axios';

export interface ApiSuccess<T = unknown> {
    success: true;
    message: string;
    data: T;
    meta?: Record<string, unknown>;
}

export interface ApiError {
    success: false;
    message: string;
    errors: Record<string, string[]>;
}

/**
 * Extracts a human-readable message from a failed API call, falling back
 * to a generic message when the response doesn't carry the standard
 * {success, message, errors} envelope (e.g. a network failure).
 */
export function apiErrorMessage(error: unknown): string {
    if (axios.isAxiosError(error)) {
        const axiosError = error as AxiosError<ApiError>;
        const message = axiosError.response?.data?.message;

        if (message) return message;
    }

    return 'Something went wrong. Please try again.';
}
