---
apply: always
---

You are working on a Laravel monolith project for an internal World Cup 2026 prediction competition.

Project name: Mundial 26 Predict.

Goal:
Build a web platform that replaces Google Sheets and Telegram private messages.

Users can:
- register and login
- see match schedule
- submit match predictions
- edit predictions before lock time
- see their points
- see leaderboard
- submit tournament nomination predictions before tournament starts

Admins can:
- manage tournaments
- manage teams
- manage matches
- enter final match results
- enter penalty shootout results if needed
- recalculate prediction points
- manage nomination categories
- enter nomination final results
- recalculate nomination points
- see leaderboard

Tech stack:
- Laravel 12
- PHP 8.3+
- PostgreSQL
- Filament admin panel
- Vue 3 frontend with Vite
- Laravel Sanctum auth
- Monolith architecture
- REST API for frontend
- Use service/action based architecture
- Keep code minimal, clean, and maintainable

Important rules:
- Do not over-engineer.
- Avoid unnecessary packages.
- Write small classes.
- Keep business logic out of controllers.
- Use Form Requests for validation.
- Use API Resources for frontend responses.
- Use DB transactions for score recalculation.
- Make recalculation idempotent.
- If recalculation is clicked multiple times, points must not duplicate.

Domain rules:
- Users predict football match score.
- Prediction closes 2 hours before match start.
- Admin enters final score after match.
- Admin may enter penalty shootout score for playoff matches.
- Main time scoring:
    - exact score: 10 points
    - correct goal difference: 4 points
    - correct result: 1 point
    - otherwise: 0 points
- Exact score has priority. Do not add 10 + 4 + 1.
- Penalty scoring:
    - exact penalty score: 10 points
    - correct penalty winner: 2 points
    - otherwise: 0 points
- Penalty points are added to main match points.
- Tournament nomination predictions:
    - best player: 30 points
    - best goalkeeper: 30 points
    - best goalkeeper conceded goals count: 30 points
    - top scorer: 30 points
    - top scorer goals count: 30 points
    - champion: 30 points
    - worst team: 30 points
- Nomination predictions close before the first tournament match starts.

Use this project context for all future tasks.
