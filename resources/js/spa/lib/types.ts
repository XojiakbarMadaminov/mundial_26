export type User = {
    id: number;
    name: string;
    email: string | null;
    telegram_username?: string | null;
    is_approved?: boolean;
};

export type Team = {
    id: number;
    name: string;
    code?: string | null;
    flag?: string | null;
};

export type Prediction = {
    id: number;
    tournament_match_id: number;
    home_score: number;
    away_score: number;
    home_penalty_score?: number | null;
    away_penalty_score?: number | null;
    points?: {
        match_points: number;
        penalty_points: number;
        total_points: number;
    };
};

export type Match = {
    id: number;
    match_number?: number | null;
    stage: string;
    group_name?: string | null;
    status: string;
    starts_at: string;
    lock_at: string;
    is_prediction_locked: boolean;
    has_penalty: boolean;
    home_team?: Team | null;
    away_team?: Team | null;
    my_prediction?: Prediction | null;
    result?: {
        home_score: number | null;
        away_score: number | null;
        home_penalty_score?: number | null;
        away_penalty_score?: number | null;
    };
    points?: {
        match_points: number;
        penalty_points: number;
        total_points: number;
    };
};

export type Tournament = {
    id: number;
    name: string;
    slug: string;
    starts_at?: string | null;
    ends_at?: string | null;
    status: string;
};

export type LeaderboardEntry = {
    id: number;
    rank?: number | null;
    user: {
        id: number;
        name?: string;
    };
    match_points: number;
    nomination_points: number;
    total_points: number;
};

export type NominationCategory = {
    id: number;
    key: string;
    name: string;
    type: 'player' | 'team' | 'number' | 'text';
    points: number;
    sort_order: number;
};

export type NominationPrediction = {
    id: number;
    nomination_category_id: number;
    value_text?: string | null;
    value_number?: number | null;
    points: number;
};
