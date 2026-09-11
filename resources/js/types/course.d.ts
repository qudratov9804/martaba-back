export type LessonType =
    | 'video'
    | 'audio'
    | 'text'
    | 'pdf'
    | 'document'
    | 'presentation'
    | 'image'
    | 'embed'
    | 'quiz'
    | 'assignment'
    | 'live'
    | 'external_link'
    | 'download'
    | 'code'
    | 'html';

export interface Lesson {
    id: number;
    course_id: number;
    section_id: number;
    title: string;
    slug: string;
    description: string | null;
    lesson_type: LessonType;
    sort_order: number;
    is_preview: boolean;
    is_required: boolean;
    is_published: boolean;
    estimated_duration_minutes: number | null;
}

export interface Section {
    id: number;
    course_id: number;
    title: string;
    description: string | null;
    sort_order: number;
    is_published: boolean;
    lessons: Lesson[];
}

export interface Course {
    id: number;
    organization_id: number;
    category_id: number | null;
    created_by: number;
    title: string;
    slug: string;
    short_description: string | null;
    description: string | null;
    level: string;
    language: string;
    pricing_type: 'free' | 'paid';
    price_minor: number;
    discount_price_minor: number | null;
    currency: string;
    status: 'draft' | 'review' | 'published' | 'unpublished' | 'archived';
    visibility: 'public' | 'private' | 'unlisted';
    certificate_enabled: boolean;
    reviews_enabled: boolean;
    published_at: string | null;
    sections: Section[];
}
