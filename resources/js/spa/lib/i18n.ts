import { ref } from 'vue';

export type Locale = 'uz' | 'ru';

type Params = Record<string, string | number>;

type Messages = Record<Locale, Record<string, string>>;

const STORAGE_KEY = 'mundial_locale';
const FALLBACK_LOCALE: Locale = 'uz';

const messages: Messages = {
    uz: {
        appName: 'Mundial 26 Predict',
        dashboard: 'Bosh sahifa',
        matches: 'O\'yinlar',
        myPredictions: 'Mening taxminlarim',
        nominations: 'Nominatsiyalar',
        leaderboard: 'Reyting',
        comparison: 'Taqqoslash',
        rules: 'Qoidalar',
        logout: 'Chiqish',
        languageUz: 'O\'zbekcha',
        languageRu: 'Русский',
        login: 'Kirish',
        register: 'Ro\'yxatdan o\'tish',
        email: 'Email',
        password: 'Parol',
        name: 'Ism',
        telegram: 'Telegram',
        phone: 'Telefon',
        confirmPassword: 'Parolni tasdiqlang',
        loginTitle: 'Mundial 26 Predict',
        loginSubtitle: 'Akkauntga kirish',
        registerTitle: 'Ro\'yxatdan o\'tish',
        registerSubtitle: 'Mundial 26 Predict akkauntingizni yarating.',
        signingIn: 'Kirish...',
        creating: 'Yaratilmoqda...',
        loginFailed: 'Kirish amalga oshmadi.',
        noAccount: 'Hisob yo\'qmi?',
        alreadyRegistered: 'Allaqachon ro\'yxatdan o\'tganmisiz?',
        moderationMessage:
            "Ma'lumotlaringiz qabul qilindi. Akkauntingiz moderatsiya jarayonida, admin tasdiqlagandan keyin login qilishingiz mumkin.",
        pendingApprovalLogin:
            'Akkauntingiz moderatsiya jarayonida. Admin tasdiqlagandan keyin login qilishingiz mumkin.',
        currentTournament: 'Joriy turnir',
        trackOverview:
            'Taxminlar, nominatsiyalar va reytingdagi o\'rningizni bir joyda kuzating.',
        totalPoints: 'Jami ball',
        rank: 'O\'rin',
        todayMatches: 'Bugungi o\'yinlar',
        allMatches: 'Barcha o\'yinlar',
        noMatchesToday: 'Bugun o\'yin yo\'q',
        predictionNeeded: 'Taxmin kerak',
        allOpenMatchesHavePredictions: 'Barcha ochiq o\'yinlarda taxminlar bor',
        top5Leaderboard: 'Top 5 reyting',
        tournamentPlaceholder: 'Turnir',
        all: 'Barchasi',
        today: 'Bugun',
        upcoming: 'Yaqinlashmoqda',
        finished: 'Yakunlangan',
        open: 'Ochiq',
        predictionOpen: 'Taxmin ochiq',
        noMatchesFound: 'O\'yin topilmadi',
        noPredictionsYet: 'Hali taxmin yo\'q',
        predictionsFromMatches:
            'Taxmin qilingan o\'yinlar va hisoblangan ballar.',
        openMatchesHint:
            'O\'yinlar sahifasidan ochiq o\'yinlarga kirib hisoblarni yuboring.',
        leaderboardIntro: 'Reyting umumiy ballar bo\'yicha.',
        leaderboardEmpty: 'Reyting hali bo\'sh',
        comparisonTitle: 'Taqqoslash',
        comparisonSubtitle:
            'O\'zingizning natijalaringizni boshqa ishtirokchi bilan yonma-yon ko\'ring.',
        comparisonOpponent: 'Raqib',
        comparisonButton: 'Taqqoslash',
        comparisonSelectPlaceholder: 'Ishtirokchini tanlang',
        comparisonMatches: 'O\'yinlar bo\'yicha',
        comparisonNominations: 'Nominatsiyalar bo\'yicha',
        yourSide: 'Siz',
        opponentSide: 'Raqib',
        noComparisonOpponents: 'Taqqoslash uchun ishtirokchilar topilmadi',
        noComparisonData: 'Bu ishtirokchilar uchun taqqoslanadigan ma\'lumot yo\'q',
        exactScoresCount: 'Aniq hisoblar',
        goalDifferenceCount: 'Farq topilgan',
        rankColumn: 'O\'rin',
        participantColumn: 'Ishtirokchi',
        matchColumn: 'O\'yin',
        nominationColumn: 'Nominatsiya',
        totalColumn: 'Jami',
        loadingMatch: 'O\'yin yuklanmoqda',
        matchNotFound: 'O\'yin topilmadi',
        start: 'Boshlanish',
        lock: 'Yopilish',
        status: 'Holat',
        yourPoints: 'Sizning ballaringiz',
        homeTeam: 'Uy jamoasi',
        awayTeam: 'Mehmon jamoa',
        homeTeamPenalties: 'Uy jamoasi penaltilari',
        awayTeamPenalties: 'Mehmon jamoa penaltilari',
        predictionSaved: 'Taxmin saqlandi.',
        couldNotSavePrediction: 'Taxminni saqlab bo\'lmadi.',
        savePrediction: 'Taxminni saqlash',
        updatePrediction: 'Taxminni yangilash',
        submitPrediction: 'Taxmin yuborish',
        saving: 'Saqlanmoqda...',
        viewMatch: 'O\'yinni ko\'rish',
        predictionLocked: 'Yopiq',
        predictionOpenStatus: 'Ochiq',
        myPrediction: 'Mening taxminim',
        noPredictionYet: 'Hali taxmin yo\'q',
        points: 'Ballar',
        pointsUnit: 'ball',
        group: 'Guruh',
        tbd: 'Belgilanmagan',
        stageGroup: 'Guruh bosqichi',
        stageRound32: '1/16 final',
        stageRound16: '1/8 final',
        stageQuarterFinal: '1/4 final',
        stageSemiFinal: '1/2 final',
        stageThirdPlace: '3-o\'rin uchun',
        stageFinal: 'Final',
        nominationsTitle: 'Nominatsiyalar',
        locksAt: 'Yopilish vaqti:',
        nominationsLock: 'Yopiladi',
        nominationsOpen: 'Ochiq',
        noNominationCategories: 'Nominatsiya kategoriyalari yo\'q',
        fillAtLeastOneNominationBeforeSaving:
            'Saqlashdan oldin kamida bitta nominatsiyani to\'ldiring.',
        filledNominationsSaved: 'To\'ldirilgan nominatsiyalar saqlandi.',
        couldNotSaveNominations: 'Nominatsiyalarni saqlab bo\'lmadi.',
        saveFilledNominations: 'To\'ldirilganlarni saqlash',
        champion: 'Chempion',
        teamName: 'Jamoa nomi',
        number: 'Son',
        playerName: 'O\'yinchi nomi',
        searchTeam: 'Jamoa qidirish',
        searchPlayer: 'O\'yinchi qidirish',
        selectTeam: 'Jamoani tanlang',
        selectPlayer: 'O\'yinchini tanlang',
        noOptionsFound: 'Variant topilmadi',
        value: 'Qiymat',
        rulesTitle: 'Qoidalar',
        rulesSubtitle: 'Mundial 26 Predict ball hisoblash tartibi.',
        scorePredictionRule: 'O\'yin hisobini taxmin qilish',
        exactScore: 'Aniq hisob topilsa:',
        goalDifference: 'Gollar farqi to\'g\'ri bo\'lsa:',
        correctResult:
            'Natija to\'g\'ri bo\'lsa, ya\'ni mezbon g\'alaba, mehmon g\'alaba yoki durang:',
        exactPriority:
            'Aniq hisob ustuvor: 10 ball berilsa, qo\'shimcha 4 yoki 1 ball qo\'shilmaydi.',
        penalties: 'Penaltilar',
        playoffOnly: 'Penalti hisobi faqat pley-off o\'yinlarida hisoblanadi.',
        penaltyExact: 'Aniq penalti hisobi:',
        penaltyWinner: 'Penalti g\'olibi to\'g\'ri bo\'lsa:',
        penaltyAdd:
            'Penalti ballari asosiy o\'yin ballariga qo\'shiladi.',
        nominationRules: 'Nominatsiya taxminlari',
        nominationRulesText:
            'Eng yaxshi futbolchi, darvozabon, to\'purar, chempion va boshqa nominatsiyalarning har biri:',
        nominationRulesChange:
            'Nominatsiyalar birinchi turnir o\'yini boshlanguncha o\'zgartirilishi mumkin.',
        nominationRulesClose:
            'Turnir boshlanganidan keyin nominatsiya taxminlari yopiladi.',
        closingTime: 'Yopilish vaqti',
        closingTimeText:
            'Har bir o\'yin uchun taxmin qabul qilish o\'yin boshlanishidan 2 soat oldin yopiladi. Vaqtlar Asia/Tashkent bo\'yicha ko\'rsatiladi.',
        matchesGroupLabel: 'Guruh',
        matchStart: 'Boshlanish',
        matchLock: 'Yopilish',
        matchStatus: 'Holat',
        matchView: 'Ko\'rish',
        matchEdit: 'Taxminni tahrirlash',
        matchSubmit: 'Taxmin yuborish',
        matchPredictionSaved: 'Taxmin saqlandi.',
        matchCouldNotSave: 'Taxminni saqlab bo\'lmadi.',
        homeScoreLabel: 'Uy jamoasi hisobi',
        awayScoreLabel: 'Mehmon jamoa hisobi',
        homePenaltyLabel: 'Uy jamoasi penaltilari',
        awayPenaltyLabel: 'Mehmon jamoa penaltilari',
        openLabel: 'Ochiq',
        lockedLabel: 'Yopiq',
        adminPending:
            'Akkauntingiz moderatsiya jarayonida. Admin tasdiqlagandan keyin login qilishingiz mumkin.',
    },
    ru: {
        appName: 'Mundial 26 Predict',
        dashboard: 'Панель',
        matches: 'Матчи',
        myPredictions: 'Мои прогнозы',
        nominations: 'Номинации',
        leaderboard: 'Рейтинг',
        comparison: 'Сравнение',
        rules: 'Правила',
        logout: 'Выйти',
        languageUz: 'O\'zbekcha',
        languageRu: 'Русский',
        login: 'Войти',
        register: 'Регистрация',
        email: 'Email',
        password: 'Пароль',
        name: 'Имя',
        telegram: 'Telegram',
        phone: 'Телефон',
        confirmPassword: 'Подтвердите пароль',
        loginTitle: 'Mundial 26 Predict',
        loginSubtitle: 'Войдите в свой аккаунт',
        registerTitle: 'Регистрация',
        registerSubtitle: 'Создайте аккаунт Mundial 26 Predict.',
        signingIn: 'Вход...',
        creating: 'Создание...',
        loginFailed: 'Вход не выполнен.',
        noAccount: 'Нет аккаунта?',
        alreadyRegistered: 'Уже зарегистрированы?',
        moderationMessage:
            'Ваши данные приняты. Аккаунт находится на модерации, войти можно после одобрения админом.',
        pendingApprovalLogin:
            'Аккаунт находится на модерации. Войти можно после одобрения админом.',
        currentTournament: 'Текущий турнир',
        trackOverview:
            'Отслеживайте прогнозы, номинации и место в рейтинге в одном месте.',
        totalPoints: 'Всего очков',
        rank: 'Место',
        todayMatches: 'Матчи сегодня',
        allMatches: 'Все матчи',
        noMatchesToday: 'Сегодня нет матчей',
        predictionNeeded: 'Нужен прогноз',
        allOpenMatchesHavePredictions: 'На всех открытых матчах уже есть прогнозы',
        top5Leaderboard: 'Топ-5 рейтинга',
        tournamentPlaceholder: 'Турнир',
        all: 'Все',
        today: 'Сегодня',
        upcoming: 'Предстоящие',
        finished: 'Завершенные',
        open: 'Открыто',
        predictionOpen: 'Прогноз открыт',
        noMatchesFound: 'Матчи не найдены',
        noPredictionsYet: 'Прогнозов пока нет',
        predictionsFromMatches: 'Отправленные прогнозы и начисленные очки.',
        openMatchesHint:
            'Откройте матчи на странице матчей и отправьте свои счета.',
        leaderboardIntro: 'Рейтинг по общему количеству очков.',
        leaderboardEmpty: 'Рейтинг пуст',
        comparisonTitle: 'Сравнение',
        comparisonSubtitle:
            'Посмотрите свои результаты рядом с другим участником.',
        comparisonOpponent: 'Соперник',
        comparisonButton: 'Сравнить',
        comparisonSelectPlaceholder: 'Выберите участника',
        comparisonMatches: 'По матчам',
        comparisonNominations: 'По номинациям',
        yourSide: 'Вы',
        opponentSide: 'Соперник',
        noComparisonOpponents: 'Для сравнения нет участников',
        noComparisonData: 'Для этих участников нет данных для сравнения',
        exactScoresCount: 'Точные счета',
        goalDifferenceCount: 'Угаданная разница',
        rankColumn: 'Место',
        participantColumn: 'Участник',
        matchColumn: 'Матч',
        nominationColumn: 'Номинация',
        totalColumn: 'Итого',
        loadingMatch: 'Загрузка матча',
        matchNotFound: 'Матч не найден',
        start: 'Начало',
        lock: 'Закрытие',
        status: 'Статус',
        yourPoints: 'Ваши очки',
        homeTeam: 'Хозяева',
        awayTeam: 'Гости',
        homeTeamPenalties: 'Пенальти хозяев',
        awayTeamPenalties: 'Пенальти гостей',
        predictionSaved: 'Прогноз сохранен.',
        couldNotSavePrediction: 'Не удалось сохранить прогноз.',
        savePrediction: 'Сохранить прогноз',
        updatePrediction: 'Обновить прогноз',
        submitPrediction: 'Отправить прогноз',
        saving: 'Сохранение...',
        viewMatch: 'Просмотр матча',
        predictionLocked: 'Закрыто',
        predictionOpenStatus: 'Открыто',
        myPrediction: 'Мой прогноз',
        noPredictionYet: 'Прогноза пока нет',
        points: 'Очки',
        pointsUnit: 'очков',
        group: 'Группа',
        tbd: 'Не определено',
        stageGroup: 'Групповой этап',
        stageRound32: '1/16 финала',
        stageRound16: '1/8 финала',
        stageQuarterFinal: '1/4 финала',
        stageSemiFinal: '1/2 финала',
        stageThirdPlace: 'За 3-е место',
        stageFinal: 'Финал',
        nominationsTitle: 'Номинации',
        locksAt: 'Время закрытия:',
        nominationsLock: 'Закрыто',
        nominationsOpen: 'Открыто',
        noNominationCategories: 'Нет категорий номинаций',
        fillAtLeastOneNominationBeforeSaving:
            'Заполните хотя бы одну номинацию перед сохранением.',
        filledNominationsSaved: 'Заполненные номинации сохранены.',
        couldNotSaveNominations: 'Не удалось сохранить номинации.',
        saveFilledNominations: 'Сохранить заполненные',
        champion: 'Чемпион',
        teamName: 'Название команды',
        number: 'Число',
        playerName: 'Имя игрока',
        searchTeam: 'Поиск команды',
        searchPlayer: 'Поиск игрока',
        selectTeam: 'Выберите команду',
        selectPlayer: 'Выберите игрока',
        noOptionsFound: 'Варианты не найдены',
        value: 'Значение',
        rulesTitle: 'Правила',
        rulesSubtitle: 'Правила начисления очков Mundial 26 Predict.',
        scorePredictionRule: 'Прогноз счета матча',
        exactScore: 'Если счет угадан точно:',
        goalDifference: 'Если угадана разница мячей:',
        correctResult:
            'Если угадан результат, то есть победа хозяев, победа гостей или ничья:',
        exactPriority:
            'Точный счет имеет приоритет: если начислено 10 очков, дополнительные 4 или 1 не добавляются.',
        penalties: 'Пенальти',
        playoffOnly: 'Пенальти учитываются только в плей-офф.',
        penaltyExact: 'Если счет пенальти угадан точно:',
        penaltyWinner: 'Если угадан победитель по пенальти:',
        penaltyAdd: 'Очки за пенальти добавляются к очкам за основное время.',
        nominationRules: 'Номинации',
        nominationRulesText:
            'Лучший игрок, вратарь, бомбардир, чемпион и другие номинации:',
        nominationRulesChange:
            'Номинации можно менять до начала первого матча турнира.',
        nominationRulesClose:
            'После начала турнира номинации закрываются.',
        closingTime: 'Время закрытия',
        closingTimeText:
            'Прогноз на каждый матч закрывается за 2 часа до начала. Время показывается по Asia/Tashkent.',
        matchesGroupLabel: 'Группа',
        matchStart: 'Начало',
        matchLock: 'Закрытие',
        matchStatus: 'Статус',
        matchView: 'Просмотр',
        matchEdit: 'Редактировать прогноз',
        matchSubmit: 'Отправить прогноз',
        matchPredictionSaved: 'Прогноз сохранен.',
        matchCouldNotSave: 'Не удалось сохранить прогноз.',
        homeScoreLabel: 'Счет хозяев',
        awayScoreLabel: 'Счет гостей',
        homePenaltyLabel: 'Пенальти хозяев',
        awayPenaltyLabel: 'Пенальти гостей',
        openLabel: 'Открыто',
        lockedLabel: 'Закрыто',
        adminPending:
            'Аккаунт находится на модерации. Войти можно после одобрения админом.',
    },
};

const locale = ref<Locale>(getInitialLocale());

function getInitialLocale(): Locale {
    if (typeof window === 'undefined') {
        return FALLBACK_LOCALE;
    }

    const stored = window.localStorage.getItem(STORAGE_KEY);

    return stored === 'ru' ? 'ru' : FALLBACK_LOCALE;
}

export function useLocale() {
    return locale;
}

export function setLocale(nextLocale: Locale): void {
    locale.value = nextLocale;

    if (typeof window !== 'undefined') {
        window.localStorage.setItem(STORAGE_KEY, nextLocale);
    }
}

export function currentLocale(): Locale {
    return locale.value;
}

export function localeToIntlTag(localeValue?: Locale): string {
    return localeValue === 'ru' ? 'ru-RU' : 'uz-UZ';
}

export function t(key: string, params: Params = {}): string {
    const template =
        messages[locale.value][key] ??
        messages[FALLBACK_LOCALE][key] ??
        key;

    return template.replace(/\{(\w+)\}/g, (_, param: string) =>
        String(params[param] ?? ''),
    );
}

export function translateStage(stage: string): string {
    const map: Record<string, string> = {
        group: t('stageGroup'),
        round_32: t('stageRound32'),
        round_16: t('stageRound16'),
        quarter_final: t('stageQuarterFinal'),
        semi_final: t('stageSemiFinal'),
        third_place: t('stageThirdPlace'),
        final: t('stageFinal'),
    };

    return map[stage] ?? stage.replaceAll('_', ' ');
}

export function translateMatchStatus(status: string): string {
    const map: Record<string, string> = {
        scheduled: t('predictionOpenStatus'),
        finished: t('lockedLabel'),
        live: t('predictionOpenStatus'),
        postponed: t('lockedLabel'),
        cancelled: t('lockedLabel'),
    };

    return map[status] ?? status;
}

export const availableLocales: Array<{ code: Locale; label: string }> = [
    { code: 'uz', label: 'UZ' },
    { code: 'ru', label: 'RU' },
];
