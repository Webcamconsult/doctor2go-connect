/* ########################################################################## */
/* SIMPLE                                                                     */
/* ########################################################################## */
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("simple-rules-form");
    const saveBtn = document.getElementById("save-simple-rules-btn");
    const jsonOutput = document.getElementById("simple-rules-json-output");
    const anchorDateInput = document.querySelector(".anchor-date-input");
    const i18n = (typeof d2gBookingRulesData !== "undefined" && d2gBookingRulesData.i18n) ? d2gBookingRulesData.i18n : {};


    if (!form || !saveBtn) return;


    const createPlusButton = () => {
        const btn = document.createElement("button");
        btn.className = "btn btn-outline-secondary btn-sm add-subslot-btn";
        btn.type = "button";
        btn.textContent = "+";
        return btn;
    };


    const createTrashButton = () => {
        const btn = document.createElement("button");
        btn.className = "btn btn-danger btn-sm remove-subslot-btn";
        btn.type = "button";
        btn.textContent = "×";
        return btn;
    };


    const createTimeSlotRow = (startTime = "09:00", endTime = "17:00") => {
        const row = document.createElement("div");
        row.className = "time-slot-row d-flex align-items-center gap-2";
        row.innerHTML = `
            <input class="form-control form-control-sm start-time-input" type="time" value="${startTime}">
            <span class="time-separator">-</span>
            <input class="form-control form-control-sm end-time-input" type="time" value="${endTime}">
        `;
        return row;
    };


    const setDayUnavailable = (slotsContainer) => {
        slotsContainer.innerHTML = `<div class="unavailable-txt text-muted">${i18n.unavailable || "Unavailable"}</div>`;
    };


    const setDayAvailable = (slotsContainer, startTime = "09:00", endTime = "17:00") => {
        slotsContainer.innerHTML = "";
        const row = createTimeSlotRow(startTime, endTime);
        slotsContainer.appendChild(row);
        syncSlotButtons(slotsContainer);
    };


    const clearActionButtons = (row) => {
        row.querySelectorAll(".add-subslot-btn, .remove-subslot-btn").forEach((btn) => btn.remove());
    };


    const syncSlotButtons = (slotsContainer) => {
        const rows = slotsContainer.querySelectorAll(".time-slot-row");


        rows.forEach((row, index) => {
            clearActionButtons(row);


            if (rows.length === 1) {
                row.appendChild(createPlusButton());
            } else if (index === rows.length - 1) {
                row.appendChild(createTrashButton());
                row.appendChild(createPlusButton());
            } else {
                row.appendChild(createTrashButton());
            }
        });
    };


    form.addEventListener("change", (e) => {
        const toggle = e.target.closest(".day-toggle");
        if (!toggle) return;


        const dayRow = toggle.closest(".day-row");
        const slotsContainer = dayRow.querySelector(".slots-container");


        if (toggle.checked) {
            setDayAvailable(slotsContainer);
        } else {
            setDayUnavailable(slotsContainer);
        }
    });


    form.addEventListener("click", (e) => {
        const addBtn = e.target.closest(".add-subslot-btn");
        const removeBtn = e.target.closest(".remove-subslot-btn");


        if (addBtn) {
            const slotsContainer = addBtn.closest(".slots-container");
            if (!slotsContainer) return;


            const newRow = createTimeSlotRow();
            slotsContainer.appendChild(newRow);
            syncSlotButtons(slotsContainer);
            return;
        }


        if (removeBtn) {
            const slotsContainer = removeBtn.closest(".slots-container");
            const dayRow = removeBtn.closest(".day-row");
            const toggle = dayRow.querySelector(".day-toggle");
            const rowToRemove = removeBtn.closest(".time-slot-row");


            if (!slotsContainer || !rowToRemove) return;


            rowToRemove.remove();


            const remainingRows = slotsContainer.querySelectorAll(".time-slot-row");


            if (remainingRows.length === 0) {
                toggle.checked = false;
                setDayUnavailable(slotsContainer);
                return;
            }


            syncSlotButtons(slotsContainer);
        }
    });


    const compileInterfaceToJSON = () => {
        const rulesArray = [];
        const rawDateVal = anchorDateInput ? String(anchorDateInput.value).trim() : "";
        let epochAnchor;


        if (!rawDateVal) {
            epochAnchor = Math.floor(Date.now() / 1000);
        } else if (/^\d+$/.test(rawDateVal)) {
            epochAnchor = parseInt(rawDateVal, 10);
        } else {
            const parsedDate = new Date(rawDateVal);
            epochAnchor = Number.isNaN(parsedDate.getTime())
                ? Math.floor(Date.now() / 1000)
                : Math.floor(parsedDate.getTime() / 1000);
        }


        const dayRows = form.querySelectorAll(".day-row");


        dayRows.forEach((row) => {
            const toggle = row.querySelector(".day-toggle");
            if (!toggle || !toggle.checked) return;


            const wdayValue = parseInt(row.dataset.wday, 10);
            const slotRows = row.querySelectorAll(".time-slot-row");


            slotRows.forEach((slot) => {
                const startVal = slot.querySelector(".start-time-input")?.value;
                const endVal = slot.querySelector(".end-time-input")?.value;


                if (startVal && endVal) {
                    rulesArray.push({
                        anchor_date: epochAnchor,
                        start_time: startVal,
                        end_time: endVal,
                        wdays: wdayValue,
                        week_interval: 1
                    });
                }
            });
        });


        return { rules: rulesArray };
    };


    const validateAllTimes = () => {
        let isValid = true;
        const dayRows = form.querySelectorAll(".day-row");


        const timeToMinutes = (timeString) => {
            if (!timeString) return null;
            const [hours, minutes] = timeString.split(":").map(Number);
            return (hours * 60) + minutes;
        };


        dayRows.forEach((row) => {
            const toggle = row.querySelector(".day-toggle");
            if (!toggle || !toggle.checked) return;


            const slotRows = row.querySelectorAll(".time-slot-row");
            const parsedSlots = [];


            slotRows.forEach((slot) => {
                const startInput = slot.querySelector(".start-time-input");
                const endInput = slot.querySelector(".end-time-input");


                if (!startInput || !endInput) return;


                startInput.setCustomValidity("");
                endInput.setCustomValidity("");


                const startMins = timeToMinutes(startInput.value);
                const endMins = timeToMinutes(endInput.value);


                if (startMins !== null && endMins !== null) {
                    if (endMins <= startMins) {
                        endInput.setCustomValidity(i18n.end_time_after_start || "End time must be after the start time.");
                        endInput.reportValidity();
                        isValid = false;
                    }


                    parsedSlots.push({
                        start: startMins,
                        end: endMins,
                        startNode: startInput,
                        endNode: endInput
                    });
                }
            });


            parsedSlots.sort((a, b) => a.start - b.start);


            for (let i = 0; i < parsedSlots.length - 1; i++) {
                const currentSlot = parsedSlots[i];
                const nextSlot = parsedSlots[i + 1];


                if (nextSlot.start < currentSlot.end) {
                    nextSlot.startNode.setCustomValidity(i18n.time_window_overlap || "This time window overlaps with an existing slot on this day.");
                    nextSlot.startNode.reportValidity();
                    isValid = false;
                }
            }
        });


        return isValid;
    };


    saveBtn.addEventListener("click", async () => {
        if (!validateAllTimes()) return;

        const finalPayload = compileInterfaceToJSON();

        if (jsonOutput) {
            jsonOutput.value = JSON.stringify(finalPayload);
        }

        const spinner = saveBtn.querySelector(".spinner-border");
        const label = saveBtn.querySelector(".btn-label");

        saveBtn.disabled = true;

        if (spinner) {
            spinner.classList.remove("d-none");
        }

        if (label) {
            label.classList.add("opacity-75");
        }

        try {
            const response = await send_simple_rules();
            console.log("Simple rules saved:", response);
            jQuery("#simple_book_msg").html(i18n.success_msg);
        } catch (error) {
            console.error("Failed to save simple rules:", error);
        } finally {
            saveBtn.disabled = false;

            if (spinner) {
                spinner.classList.add("d-none");
            }

            if (label) {
                label.classList.remove("opacity-75");
            }
        }
    });


    const renderSimpleInterfaceFromJSON = () => {
        if (!jsonOutput || !jsonOutput.value) {
            form.querySelectorAll(".slots-container").forEach((slotsContainer) => {
                const row = slotsContainer.querySelector(".time-slot-row");
                if (row) syncSlotButtons(slotsContainer);
            });
            return;
        }


        let rules = [];


        try {
            const parsed = JSON.parse(jsonOutput.value);
            rules = parsed.rules || [];
        } catch (e) {
            console.error(i18n.parse_incoming_rules_error || "Could not parse incoming rules data for simple layout:", e);
            return;
        }


        const rulesByDay = {};


        rules.forEach((rule) => {
            if (rule.wdays !== undefined) {
                if (!rulesByDay[rule.wdays]) rulesByDay[rule.wdays] = [];
                rulesByDay[rule.wdays].push(rule);
            }
        });


        const dayRows = form.querySelectorAll(".day-row");


        dayRows.forEach((row) => {
            const wdayIndex = parseInt(row.dataset.wday, 10);
            const toggle = row.querySelector(".day-toggle");
            const slotsContainer = row.querySelector(".slots-container");
            const dayRules = rulesByDay[wdayIndex];


            if (!dayRules || dayRules.length === 0) {
                toggle.checked = false;
                setDayUnavailable(slotsContainer);
                return;
            }


            toggle.checked = true;
            slotsContainer.innerHTML = "";


            dayRules.forEach((rule) => {
                const slotRow = createTimeSlotRow(rule.start_time, rule.end_time);
                slotsContainer.appendChild(slotRow);
            });


            syncSlotButtons(slotsContainer);
        });
    };


    renderSimpleInterfaceFromJSON();
});


// Async function that uses jQuery $.post with a callback
async function send_simple_rules() {
    var dsr = typeof d2gBookingRulesData !== "undefined" ? d2gBookingRulesData : null;
    var i18n = dsr && dsr.i18n ? dsr.i18n : {};


    if (!dsr || !dsr.ajax || !dsr.ajax.url) {
        throw new Error(i18n.missing_ajax_url || "d2gBookingRulesData.ajax.url is missing");
    }


    var data = {
        action: "d2gc_update_booking_rules",
        send_booking_rules_nonce: dsr.ajax.send_booking_rules_nonce,
        simple_rules_json: jQuery("#simple-rules-json-output").val(),
        wp_doc_id: jQuery("#doc_id_simple").val(),
        anchor_date: jQuery("#anchor-date-input-simple").val()
    };


    console.log("Sending simple rules:", data);
    


    return await new Promise((resolve, reject) => {
        jQuery.post(dsr.ajax.url, data, function (response) {
            if (response && response.success) {
                resolve(response);
            } else {
                reject(response || { error: i18n.request_failed || "Request failed" });
            }
        }).fail(function (error) {
            reject(error);
        });
    });
    
}


/* ########################################################################## */
/* ADVANCED                                                                   */
/* ########################################################################## */
document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form");
    const addRuleBtn = document.getElementById("add-rule-btn");
    const typeSelect = document.getElementById("rule-type-select");
    const rulesContainer = document.getElementById("rules-container");
    const jsonOutput = document.getElementById("rules-json-output");
    const i18n = (typeof d2gBookingRulesData !== "undefined" && d2gBookingRulesData.i18n) ? d2gBookingRulesData.i18n : {};


    if (!addRuleBtn) return;


    const sections = {
        weekly: document.getElementById("weekly-options"),
        monthly_relative: document.getElementById("monthly-relative-options"),
        monthly_fixed: document.getElementById("monthly-fixed-options")
    };


    let rulesArray = [];
    let editingIndex = null;


    if (jsonOutput && jsonOutput.value) {
        try {
            const parsed = JSON.parse(jsonOutput.value);
            rulesArray = parsed.rules || [];
            renderRulesList();
        } catch (e) {
            console.warn(i18n.parse_existing_rules_error || "Could not parse existing rules:", e);
        }
    }


    const toggleSections = () => {
        if (!typeSelect) return;


        Object.keys(sections).forEach((key) => {
            if (sections[key]) {
                sections[key].style.display = typeSelect.value === key ? "block" : "none";
            }
        });
    };


    if (typeSelect) {
        typeSelect.addEventListener("change", toggleSections);
        toggleSections();
    }


    const validateTimes = () => {
        const startTimeInput = document.querySelector(".start-time-input");
        const endTimeInput = document.querySelector(".end-time-input");


        if (!startTimeInput || !endTimeInput) return true;


        endTimeInput.setCustomValidity("");
        const start = startTimeInput.value;
        const end = endTimeInput.value;


        if (start && end && end <= start) {
            endTimeInput.setCustomValidity(i18n.end_time_after_start || "End time must be after the start time.");
            endTimeInput.reportValidity();
            return false;
        }


        return true;
    };


    const compileRuleFromForm = () => {
        const type = typeSelect ? typeSelect.value : "weekly";
        const anchorDateInput = document.querySelector(".anchor-date-input");
        const startTimeInput = document.querySelector(".start-time-input");
        const endTimeInput = document.querySelector(".end-time-input");


        const dateVal = anchorDateInput ? anchorDateInput.value : new Date().toISOString().split("T")[0];
        const epochAnchor = Math.floor(new Date(dateVal).getTime() / 1000);


        let rule = {
            anchor_date: epochAnchor,
            start_time: startTimeInput ? startTimeInput.value : "00:00",
            end_time: endTimeInput ? endTimeInput.value : "00:00"
        };


        if (type === "weekly") {
            const wdayEl = sections.weekly.querySelector(".wday-input");
            const intervalEl = sections.weekly.querySelector(".week-interval-input");
            rule.wdays = wdayEl ? parseInt(wdayEl.value, 10) : 0;
            rule.week_interval = intervalEl ? parseInt(intervalEl.value, 10) : 1;
        } else if (type === "monthly_relative") {
            const womEl = sections.monthly_relative.querySelector(".week-of-month-input");
            const wdayEl = sections.monthly_relative.querySelector(".wday-input");
            rule.week_of_month = womEl ? parseInt(womEl.value, 10) : 1;
            rule.wdays = wdayEl ? parseInt(wdayEl.value, 10) : 0;
        } else if (type === "monthly_fixed") {
            const mdayEl = sections.monthly_fixed.querySelector(".mday-input");
            const intervalEl = sections.monthly_fixed.querySelector(".month-interval-input");
            rule.mdays = mdayEl ? parseInt(mdayEl.value, 10) : 1;
            rule.month_interval = intervalEl ? parseInt(intervalEl.value, 10) : 1;
        }


        return rule;
    };


    const resetFormValues = () => {
        editingIndex = null;
        addRuleBtn.textContent = i18n.add_rule_to_list || "Add Rule to List";
        addRuleBtn.style.backgroundColor = "";


        const endTimeInput = document.querySelector(".end-time-input");
        if (endTimeInput) endTimeInput.setCustomValidity("");
    };


    addRuleBtn.addEventListener("click", () => {
        if (!validateTimes()) return;


        const compiledRule = compileRuleFromForm();


        if (editingIndex !== null) {
            rulesArray[editingIndex] = compiledRule;
        } else {
            rulesArray.push(compiledRule);
        }


        if (jsonOutput) jsonOutput.value = JSON.stringify({ rules: rulesArray });


        resetFormValues();
        renderRulesList();
    });


    function getRuleLabel(rule) {
        const days = [
            i18n.sunday || "Sunday",
            i18n.monday || "Monday",
            i18n.tuesday || "Tuesday",
            i18n.wednesday || "Wednesday",
            i18n.thursday || "Thursday",
            i18n.friday || "Friday",
            i18n.saturday || "Saturday"
        ];
        const weeks = {
            "1": i18n["1st"] || "1st",
            "2": i18n["2nd"] || "2nd",
            "3": i18n["3rd"] || "3rd",
            "4": i18n["4th"] || "4th",
            "-1": i18n.last || "Last"
        };
        const timeStr = `${i18n.between || "between"} ${rule.start_time} ${i18n.and || "and"} ${rule.end_time}`;


        if (rule.week_interval !== undefined) {
            const dayName = days[rule.wdays];
            return `${i18n.every || "Every"} ${rule.week_interval > 1 ? rule.week_interval + " " + (i18n.weeks || "weeks") : (i18n.week || "week")} ${i18n.on || "on"} ${dayName} ${timeStr}`;
        } else if (rule.week_of_month !== undefined) {
            const dayName = days[rule.wdays];
            return `${i18n.every || "Every"} ${weeks[rule.week_of_month]} ${dayName} ${i18n.of_the_month || "of the month"} ${timeStr}`;
        } else if (rule.mdays !== undefined) {
            return `${i18n.every || "Every"} ${rule.month_interval > 1 ? rule.month_interval + " " + (i18n.months || "months") : (i18n.month || "month")} ${i18n.on || "on"} ${i18n.the || "the"} ${rule.mdays}${i18n.th_day || "th day"} ${timeStr}`;
        }


        return i18n.custom_rule || "Custom Rule";
    }


    function renderRulesList() {
        if (!rulesContainer) return;
        rulesContainer.innerHTML = "";


        const template = document.getElementById("rule-item-template");
        if (!template) return;


        rulesArray.forEach((rule, index) => {
            const clone = template.content.cloneNode(true);
            const listItem = clone.querySelector(".rule-item");


            clone.querySelector(".rule-text").textContent = getRuleLabel(rule);


            if (editingIndex === index) {
                listItem.classList.add("editing-active");
                listItem.style.borderLeft = "4px solid #3b82f6";
            }


            clone.querySelector(".remove-rule-btn").addEventListener("click", () => {
                if (editingIndex === index) resetFormValues();


                rulesArray.splice(index, 1);
                if (jsonOutput) jsonOutput.value = JSON.stringify({ rules: rulesArray });
                renderRulesList();
            });


            clone.querySelector(".edit-rule-btn").addEventListener("click", () => {
                editingIndex = index;
                addRuleBtn.textContent = i18n.update_rule || "Update Rule";


                const startTimeInput = document.querySelector(".start-time-input");
                const endTimeInput = document.querySelector(".end-time-input");
                const anchorDateInput = document.querySelector(".anchor-date-input");


                if (startTimeInput) startTimeInput.value = rule.start_time;
                if (endTimeInput) endTimeInput.value = rule.end_time;


                if (anchorDateInput) {
                    const d = new Date(rule.anchor_date * 1000);
                    anchorDateInput.value = d.toISOString().split("T")[0];
                }


                if (rule.week_interval !== undefined && typeSelect) {
                    typeSelect.value = "weekly";
                    sections.weekly.querySelector(".wday-input").value = rule.wdays;
                    sections.weekly.querySelector(".week-interval-input").value = rule.week_interval;
                } else if (rule.week_of_month !== undefined && typeSelect) {
                    typeSelect.value = "monthly_relative";
                    sections.monthly_relative.querySelector(".week-of-month-input").value = rule.week_of_month;
                    sections.monthly_relative.querySelector(".wday-input").value = rule.wdays;
                } else if (rule.mdays !== undefined && typeSelect) {
                    typeSelect.value = "monthly_fixed";
                    sections.monthly_fixed.querySelector(".mday-input").value = rule.mdays;
                    sections.monthly_fixed.querySelector(".month-interval-input").value = rule.month_interval;
                }


                toggleSections();
                renderRulesList();
            });


            rulesContainer.appendChild(clone);
        });
    }
});