'use strict';

import React from "react";
import PropTypes from 'prop-types';
import {FormElement, HelpText, FormElementLabel} from './InputRow';
import Autocomplete from "./Autocomplete";

export default function ChoiceRow(props) {
    const {
        form,
        translations,
        widget_only,
        handleFormChange,
        fetchChoices,
        section
    } = props;

    function getLabelClass(child) {
        var result = 'form_label';
        if (child.required === 'Required') {
            result += ' required';
        }
        return result;
    }

    if (widget_only) {
        return (
            <Select styles={{height: '30px', width: '100%', borderRadius: '10px'}} defaultValue={form.value} value={form.value} placeholder={form.placeholder} options={getChoices(form)} />
        );
    }

    //onChange={(e) => handleChange(e, form)} type={form.type} id={form.id} name={form.full_name} required={getRequiredAttribute(form)} defaultValue={form.value}

    return (
        <FormElement>
            <FormElementLabel htmlFor={form.id}>{form.label}</FormElementLabel>
            <br />
            <Autocomplete
                suggestions={getChoices(form)}
                translations={translations}
                form={getForm(form)}
                handleFormChange={handleFormChange}
                section={section}
                fetchChoices={fetchChoices}
                />
            <br />
            <HelpText id={`${form.id}_help`} className="help-text">{form.help}</HelpText>
        </FormElement>
    )
}

export function getForm(form) {
    form = {...form};
    if (typeof form.state !== 'object') {
        form['state'] = {
            activeSuggestion: 0,
            filteredSuggestions: [],
            showSuggestions: false,
            userInput: ""
        };
        if (!(typeof form.value === 'object' || typeof form.value === 'array')) {
            form.state.userInput = getLabelOfValue(form);
        }
    }
    return form;
}

function getLabelOfValue(form) {
    let value = form.value;
    let label = '';
    getChoices(form).filter((suggestion) => {
        if (suggestion.value.toLowerCase() === value.toLowerCase()) label = suggestion.label;
    })
    if (label === '') label = value;
    return label;
}


export function getRequiredAttribute(child) {
    let result = '';
    if (child.required === 'Required') {
        result = 'required';
    }
    return result;
}

export function getChoices(form) {
    const choices = {...form.choices};
    let result = [];
    if (typeof choices === "object") {
        Object.keys(choices).map(i => {
            const choice = choices[i];
            result.push({value: choice.value, label: choice.label});
        })
    } else {
        choices.map((choice, i) => {
            result.push({value: choice.value, label: choice.label});
        })
    }
    return result;
}

ChoiceRow.propTypes = {
    translations: PropTypes.object.isRequired,
    form: PropTypes.object.isRequired,
    handleFormChange: PropTypes.func.isRequired,
    fetchChoices: PropTypes.func.isRequired,
    widget_only: PropTypes.bool,
    section: PropTypes.string.isRequired,
}
ChoiceRow.defaultTypes = {
    widget_only: false
}
