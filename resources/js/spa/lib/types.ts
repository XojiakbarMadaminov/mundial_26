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
    previous_rank?: number | null;
    rank_changed_at?: string | null;
    user: {
        id: number;
        name?: string;
    };
    match_points: number;
    nomination_points: number;
    total_points: number;
};

export type ComparisonParticipant = {
    id: number;
    name: string;
    rank?: number | null;
    match_points: number;
    nomination_points: number;
    total_points: number;
    exact_scores_count: number;
    goal_difference_count: number;
    result_count: number;
};

export type ComparisonPrediction = {
    id: number;
    home_score?: number | null;
    away_score?: number | null;
    home_penalty_score?: number | null;
    away_penalty_score?: number | null;
    submitted_at?: string | null;
    calculated_at?: string | null;
    points?: {
        match_points: number;
        penalty_points: number;
        total_points: number;
    } | null;
};

export type ComparisonMatch = {
    id: number;
    stage: string;
    group_name?: string | null;
    status: string;
    starts_at: string;
    lock_at: string;
    home_team: Team;
    away_team: Team;
    result?: {
        home_score: number | null;
        away_score: number | null;
        home_penalty_score?: number | null;
        away_penalty_score?: number | null;
    } | null;
    me_prediction?: ComparisonPrediction | null;
    opponent_prediction?: ComparisonPrediction | null;
};

export type ComparisonNomination = {
    id: number;
    key: string;
    name: string;
    type: 'player' | 'team' | 'number' | 'text';
    points: number;
    me_prediction?: {
        id: number;
        player?: {
            id: number;
            name: string;
        } | null;
        team?: {
            id: number;
            name: string;
        } | null;
        value_text?: string | null;
        value_number?: number | null;
        points: number;
        calculated_at?: string | null;
    } | null;
    opponent_prediction?: {
        id: number;
        player?: {
            id: number;
            name: string;
        } | null;
        team?: {
            id: number;
            name: string;
        } | null;
        value_text?: string | null;
        value_number?: number | null;
        points: number;
        calculated_at?: string | null;
    } | null;
};

export type ComparisonResponse = {
    tournament: Tournament;
    me: ComparisonParticipant;
    opponent: ComparisonParticipant;
    matches: ComparisonMatch[];
    nominations: ComparisonNomination[];
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
    player_id?: number | null;
    player?: {
        id: number;
        name: string;
    } | null;
    team_id?: number | null;
    team?: {
        id: number;
        name: string;
    } | null;
    value_text?: string | null;
    value_number?: number | null;
    points: number;
};
