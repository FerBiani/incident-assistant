export type IncidentSeverity = 'low' | 'medium' | 'high' | 'critical';
export type IncidentStatus = 'open' | 'investigating' | 'resolved';
export type IncidentAnalysisStatus =
    | 'not_started'
    | 'pending'
    | 'completed'
    | 'failed';
export type IncidentNoteSource = 'user' | 'ai';
export type ConversationRole = 'user' | 'assistant';
export type ApprovalStatus = 'pending' | 'approved' | 'rejected' | 'failed';

export interface Project {
    id: number;
    name: string;
    description: string | null;
    incidents_count?: number;
}

export interface IncidentNote {
    id: number;
    content: string;
    source: IncidentNoteSource;
    source_label: string;
    created_at: string;
}

export interface IncidentAnalysis {
    status: IncidentAnalysisStatus;
    summary: string | null;
    suggested_severity: IncidentSeverity | null;
    suggested_severity_label: string | null;
    probable_causes: string[];
    recommended_actions: string[];
    analyzed_at: string | null;
}

export interface IncidentApproval {
    id: string;
    tool: 'AddIncidentNote';
    description: string;
    arguments: { content?: string };
    result: unknown;
    status: ApprovalStatus;
}

export interface IncidentConversationMessage {
    id: string;
    role: ConversationRole;
    content: string;
    created_at: string;
    approvals: IncidentApproval[];
}

export interface IncidentConversation {
    id: string;
    messages: IncidentConversationMessage[];
}

export type ApprovalDecision = 'approve' | 'reject';

export interface ToolApprovalRequest {
    id: string;
    tool: string;
    arguments: Record<string, unknown>;
    reason: string | null;
}

export interface IncidentStreamEvent {
    type: string;
    delta?: string;
    approvals?: ToolApprovalRequest[];
    tool_id?: string;
    successful?: boolean;
    denied?: boolean;
    message?: string;
    error?: string | null;
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
    analysis: IncidentAnalysis;
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
