export type IncidentSeverity = 'low' | 'medium' | 'high' | 'critical';
export type IncidentStatus = 'open' | 'investigating' | 'resolved';

export interface Project {
    id: number;
    name: string;
    description: string | null;
    incidents_count?: number;
}

export interface IncidentNote {
    id: number;
    content: string;
    created_at: string;
}

export interface IncidentSummary {
    id: number;
    project: Project;
    title: string;
    severity: IncidentSeverity;
    severity_label: string;
    status: IncidentStatus;
    status_label: string;
    created_at: string;
}

export interface Incident extends IncidentSummary {
    description: string | null;
    logs: string | null;
    notes: IncidentNote[];
    updated_at: string;
}

export interface SelectOption<T extends string | number = string> {
    value: T;
    label: string;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface Paginated<T> {
    data: T[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        from: number | null;
        last_page: number;
        links: PaginationLink[];
        path: string;
        per_page: number;
        to: number | null;
        total: number;
    };
}
