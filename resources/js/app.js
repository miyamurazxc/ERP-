// Этот JavaScript отвечает за подсчет дней в форме создания заявки.
// Он работает только на странице, где есть форма с атрибутом data-day-calculator.

// Ждем, пока браузер полностью загрузит HTML-страницу.
document.addEventListener('DOMContentLoaded', () => {
    // Ищем форму создания заявки.
    const form = document.querySelector('[data-day-calculator]');

    // Если такой формы на странице нет, прекращаем выполнение кода.
    if (!form) {
        return;
    }

    // Поле "Дата начала".
    const startInput = form.querySelector('[name="start_date"]');
    // Поле "Дата конца".
    const endInput = form.querySelector('[name="end_date"]');
    // Элемент, куда выводятся календарные дни.
    const calendarOutput = form.querySelector('[data-calendar-days]');
    // Элемент, куда выводятся рабочие дни.
    const workingOutput = form.querySelector('[data-working-days]');
    // Блок предупреждения о пересечении дат.
    const overlapWarning = form.querySelector('[data-overlap-warning]');
    // Блок календаря хранит занятые периоды в JSON.
    const busyCalendar = form.querySelector('[data-busy-calendar]');
    // Превращаем JSON занятых периодов в массив JavaScript.
    const busyRequests = busyCalendar ? JSON.parse(busyCalendar.dataset.busyCalendar || '[]') : [];
    // Праздники приходят из Laravel. Их не считаем рабочими днями.
    const holidays = JSON.parse(form.dataset.holidays || '[]');
    // Форматирует дату как YYYY-MM-DD без сдвига часового пояса.
    const formatDateKey = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    };

    // Функция пересчитывает дни каждый раз, когда меняется дата.
    const updateDays = () => {
        // Превращаем дату начала из строки в объект Date.
        const start = startInput.value ? new Date(`${startInput.value}T00:00:00`) : null;
        // Превращаем дату конца из строки в объект Date.
        const end = endInput.value ? new Date(`${endInput.value}T00:00:00`) : null;

        // Если дата не выбрана или конец раньше начала, показываем нули.
        if (!start || !end || end < start) {
            calendarOutput.textContent = '0';
            workingOutput.textContent = '0';
            overlapWarning.hidden = true;
            return;
        }

        // Счетчик календарных дней.
        let calendarDays = 0;
        // Счетчик рабочих дней.
        let workingDays = 0;
        // current будет двигаться от даты начала к дате конца.
        const current = new Date(start);

        // Цикл идет по каждому дню выбранного периода.
        while (current <= end) {
            // Каждый день считается календарным.
            calendarDays += 1;
            // getDay() возвращает день недели: 0 - воскресенье, 6 - суббота.
            const day = current.getDay();
            // Дата в формате YYYY-MM-DD нужна, чтобы сравнить ее со списком праздников.
            const dateKey = formatDateKey(current);

            // Если это не воскресенье, не суббота и не праздник, день считается рабочим.
            if (day !== 0 && day !== 6 && !holidays.includes(dateKey)) {
                workingDays += 1;
            }

            // Переходим к следующему дню.
            current.setDate(current.getDate() + 1);
        }

        // Показываем календарные дни на странице.
        calendarOutput.textContent = calendarDays;
        // Показываем рабочие дни на странице.
        workingOutput.textContent = workingDays;

        // Проверяем пересечение выбранного периода с уже занятыми периодами.
        const hasOverlap = busyRequests.some((busy) => {
            const busyStart = new Date(`${busy.start_date}T00:00:00`);
            const busyEnd = new Date(`${busy.end_date}T00:00:00`);

            return busyStart <= end && busyEnd >= start;
        });

        // Показываем или скрываем предупреждение.
        overlapWarning.hidden = !hasOverlap;
    };

    // Пересчитываем дни при изменении даты начала.
    startInput.addEventListener('change', updateDays);
    // Пересчитываем дни при изменении даты конца.
    endInput.addEventListener('change', updateDays);
    // Запускаем расчет сразу при загрузке страницы.
    updateDays();
});

// Этот блок отвечает за большой календарь на странице создания заявки:
// показывает дни месяца, праздники, занятые периоды и выбранный диапазон дат.
document.addEventListener('DOMContentLoaded', () => {
    // Ищем блок календаря на странице.
    const calendar = document.querySelector('[data-vacation-calendar]');

    // Если календаря на странице нет, прекращаем выполнение кода.
    if (!calendar) {
        return;
    }

    // Форма создания заявки нужна, чтобы читать и менять даты.
    const form = document.querySelector('[data-day-calculator]');
    // Поле "Дата начала".
    const startInput = form?.querySelector('[name="start_date"]');
    // Поле "Дата конца".
    const endInput = form?.querySelector('[name="end_date"]');
    // Select с типом документа: отпуск или больничный.
    const typeSelect = form?.querySelector('[name="type"]');

    // Праздники и занятые периоды приходят из Laravel в виде JSON.
    const holidays = JSON.parse(calendar.dataset.holidays || '[]');
    const busy = JSON.parse(calendar.dataset.busy || '[]');

    // Элементы шапки и сетки календаря.
    const monthLabel = calendar.querySelector('[data-vc-month-label]');
    const grid = calendar.querySelector('[data-vc-grid]');
    const prevBtn = calendar.querySelector('[data-vc-prev]');
    const nextBtn = calendar.querySelector('[data-vc-next]');
    const todayBtn = calendar.querySelector('[data-vc-today]');

    // Панель баланса отпуска показывается только для типа "отпуск".
    const balancePanel = document.querySelector('[data-vacation-balance]');
    const balance = balancePanel ? JSON.parse(balancePanel.dataset.balance || '{}') : null;
    const balanceWarning = balancePanel?.querySelector('[data-balance-warning]');

    // Русские названия месяцев для заголовка календаря.
    const MONTH_NAMES = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
    // Эти статусы заявки считаются "на проверке" для подсветки в календаре.
    const PENDING_STATUSES = ['pending_hr', 'pending_director'];

    // Форматирует дату как YYYY-MM-DD без сдвига часового пояса.
    const formatDateKey = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    };

    // Превращает строку YYYY-MM-DD в объект Date в локальном времени.
    const parseDateKey = (key) => {
        const [year, month, day] = key.split('-').map(Number);

        return new Date(year, month - 1, day);
    };

    // viewDate хранит месяц, который сейчас показан в календаре.
    const viewDate = startInput?.value ? parseDateKey(startInput.value) : new Date();
    viewDate.setDate(1);

    // Обновляет предупреждение под балансом отпуска.
    const updateBalanceWarning = () => {
        if (!balance || !balanceWarning || !typeSelect) {
            return;
        }

        if (typeSelect.value !== 'vacation') {
            balanceWarning.hidden = true;
            return;
        }

        const days = Number(form?.querySelector('[data-calendar-days]')?.textContent || 0);

        if (days === 0) {
            balanceWarning.hidden = true;
            return;
        }

        if (balance.is_first && days < balance.min_first_days) {
            balanceWarning.textContent = `Первый отпуск в году должен быть не менее ${balance.min_first_days} дней (выбрано ${days}).`;
            balanceWarning.hidden = false;
            return;
        }

        if (days > balance.remaining) {
            balanceWarning.textContent = `Превышен остаток отпуска: доступно ${balance.remaining} из ${balance.limit} дней.`;
            balanceWarning.hidden = false;
            return;
        }

        balanceWarning.hidden = true;
    };

    // Перестраивает сетку календаря для месяца из viewDate.
    const render = () => {
        const year = viewDate.getFullYear();
        const month = viewDate.getMonth();
        const todayKey = formatDateKey(new Date());
        const selStart = startInput?.value || null;
        const selEnd = endInput?.value || null;

        monthLabel.textContent = `${MONTH_NAMES[month]} ${year}`;

        const firstWeekday = (new Date(year, month, 1).getDay() + 6) % 7;
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const totalCells = Math.ceil((firstWeekday + daysInMonth) / 7) * 7;

        grid.innerHTML = '';

        for (let i = 0; i < totalCells; i++) {
            const dayNumber = i - firstWeekday + 1;
            const date = new Date(year, month, dayNumber);
            const dateKey = formatDateKey(date);

            const cell = document.createElement('button');
            cell.type = 'button';
            cell.className = 'vc-day';
            cell.textContent = String(date.getDate());
            cell.title = dateKey;

            if (date.getMonth() !== month) {
                cell.classList.add('is-other-month');
            }

            if (date.getDay() === 0 || date.getDay() === 6) {
                cell.classList.add('is-weekend');
            }

            if (holidays.includes(dateKey)) {
                cell.classList.add('is-holiday');
            }

            if (dateKey === todayKey) {
                cell.classList.add('is-today');
            }

            const busyItem = busy.find((item) => dateKey >= item.start_date && dateKey <= item.end_date);

            if (busyItem) {
                cell.classList.add('is-busy', `is-busy-${busyItem.type}`);
                cell.classList.add(PENDING_STATUSES.includes(busyItem.status_key) ? 'is-busy-pending' : 'is-busy-approved');
                cell.title = `${dateKey}: ${busyItem.label}, ${busyItem.status}`;
            }

            if (selStart && selEnd && dateKey >= selStart && dateKey <= selEnd) {
                cell.classList.add('is-selected');

                if (dateKey === selStart) {
                    cell.classList.add('is-selected-start');
                }

                if (dateKey === selEnd) {
                    cell.classList.add('is-selected-end');
                }
            } else if (selStart && !selEnd && dateKey === selStart) {
                cell.classList.add('is-selected', 'is-selected-start', 'is-selected-end');
            }

            cell.addEventListener('click', () => selectDate(dateKey));
            grid.appendChild(cell);
        }
    };

    // Обрабатывает клик по дню и обновляет поля формы.
    const selectDate = (dateKey) => {
        if (!startInput || !endInput) {
            return;
        }

        const hasFullRange = startInput.value && endInput.value;

        if (!startInput.value || hasFullRange) {
            startInput.value = dateKey;
            endInput.value = '';
        } else if (dateKey < startInput.value) {
            startInput.value = dateKey;
            endInput.value = '';
        } else {
            endInput.value = dateKey;
        }

        startInput.dispatchEvent(new Event('change', { bubbles: true }));
        endInput.dispatchEvent(new Event('change', { bubbles: true }));

        render();
        updateBalanceWarning();
    };

    typeSelect?.addEventListener('change', () => {
        if (balancePanel) {
            balancePanel.hidden = typeSelect.value !== 'vacation';
        }

        updateBalanceWarning();
    });

    prevBtn?.addEventListener('click', () => {
        viewDate.setMonth(viewDate.getMonth() - 1);
        render();
    });

    nextBtn?.addEventListener('click', () => {
        viewDate.setMonth(viewDate.getMonth() + 1);
        render();
    });

    todayBtn?.addEventListener('click', () => {
        const now = new Date();
        viewDate.setFullYear(now.getFullYear(), now.getMonth(), 1);
        render();
    });

    startInput?.addEventListener('change', () => {
        if (startInput.value) {
            const parsed = parseDateKey(startInput.value);
            viewDate.setFullYear(parsed.getFullYear(), parsed.getMonth(), 1);
        }

        render();
        updateBalanceWarning();
    });

    endInput?.addEventListener('change', () => {
        render();
        updateBalanceWarning();
    });

    render();
    updateBalanceWarning();
});

// Этот блок отвечает за автоматический выбор прав в админке при смене роли.
document.addEventListener('DOMContentLoaded', () => {
    // Ищем все формы, где есть выбор роли и список разрешений.
    const permissionForms = document.querySelectorAll('[data-role-permissions-form]');

    // Если мы не на странице админки, таких форм не будет и код просто не выполнится.
    permissionForms.forEach((form) => {
        // В data-role-permissions лежит JSON: какая роль какие права должна получать по умолчанию.
        const permissionsByRole = JSON.parse(form.dataset.rolePermissions || '{}');
        // Select с ролью пользователя.
        const roleSelect = form.querySelector('[name="role"]');

        // Если в форме почему-то нет select роли, пропускаем ее.
        if (!roleSelect) {
            return;
        }

        // Функция находит все чекбоксы разрешений, которые относятся к этой форме.
        const permissionCheckboxes = () => {
            // Для формы создания чекбоксы находятся прямо внутри формы.
            const insideForm = Array.from(form.querySelectorAll('[name="permissions[]"]'));

            // Для таблицы пользователей чекбоксы могут быть снаружи формы, но привязаны атрибутом form="user-form-id".
            if (!form.id) {
                return insideForm;
            }

            const linkedByFormId = Array.from(document.querySelectorAll(`[form="${form.id}"][name="permissions[]"]`));

            // Объединяем оба варианта и убираем дубли.
            return Array.from(new Set([...insideForm, ...linkedByFormId]));
        };

        // Функция включает только те права, которые соответствуют выбранной роли.
        const applyRolePermissions = () => {
            // Берем роль из select.
            const selectedRole = roleSelect.value;
            // Получаем стандартные права выбранной роли.
            const defaultPermissions = permissionsByRole[selectedRole] || [];

            // Проходим по каждому чекбоксу и ставим галочку, если его value есть в списке стандартных прав.
            permissionCheckboxes().forEach((checkbox) => {
                checkbox.checked = defaultPermissions.includes(checkbox.value);
            });
        };

        // Автозаполнение срабатывает каждый раз, когда админ меняет роль.
        roleSelect.addEventListener('change', applyRolePermissions);
    });
});

// Этот блок открывает и скрывает форму ответа под конкретным комментарием.
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-reply-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const form = document.getElementById(button.dataset.replyToggle);

            if (!form) {
                return;
            }

            form.hidden = !form.hidden;
            button.textContent = form.hidden ? 'Ответить' : 'Скрыть ответ';
        });
    });
});

// Этот блок управляет модальными окнами редактирования пользователей в админке.
document.addEventListener('DOMContentLoaded', () => {
    const openButtons = document.querySelectorAll('[data-modal-open]');
    const closeButtons = document.querySelectorAll('[data-modal-close]');
    const modals = document.querySelectorAll('.modal-backdrop');

    openButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const modal = document.getElementById(button.dataset.modalOpen);

            if (modal) {
                modal.hidden = false;
            }
        });
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal-backdrop');

            if (modal) {
                modal.hidden = true;
            }
        });
    });

    modals.forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                modal.hidden = true;
            }
        });
    });
});
