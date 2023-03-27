'use strict';

import React from "react"
import PropTypes from 'prop-types'
import {FormElement, HelpText, FormElementLabel} from './InputRow'
import styled from 'styled-components';


const FormElementSelect = styled.select`
    height: 30px;
    width: 100%;
    border-radius: 10px;
`

export default function ChoiceRow(props) {
    const {
        form,
        translations,
        widget_only,
        handleChange
    } = props;

    function getLabelClass(child) {
        var result = 'form_label';
        if (child.required === 'Required') {
            result += ' required';
        }
        return result;
    }

    function getRequiredAttribute(child) {
        let result = '';
        if (child.required === 'Required') {
            result = 'required';
        }
        return result;
    }

    function getChoices(choices) {
        let result = [];
        if (form.placeholder) {
            result.push(<option key='placeholder' value=''>{form.placeholder}</option>);
        }
        if (typeof choices === "object") {
            Object.keys(choices).map(i => {
                const choice = choices[i];
                result.push(<option key={i} value={choice.value}>{choice.label}</option>);
            })
        } else {
            choices.map((choice, i) => {
                result.push(<option key={i} value={choice.value}>{choice.label}</option>);
            })
        }
        return result;
    }

    if (widget_only) {
        return (
            <FormElementSelect onChange={(e) => handleChange(e, form)} type={form.type} id={form.id} name={form.full_name} required={getRequiredAttribute(form)} defaultValue={form.value} >{getChoices(form.choices)}</FormElementSelect>
        );
    }

    return (
        <FormElement>
            <FormElementLabel htmlFor={form.id}>{form.label}</FormElementLabel>
            <br />
            <FormElementSelect onChange={(e) => handleChange(e, form)} type={form.type} id={form.id} name={form.full_name} required={getRequiredAttribute(form)} defaultValue={form.value} >{getChoices(form.choices)}</FormElementSelect>
            <br />
            <HelpText id={`${form.id}_help`} className="help-text">{form.help}</HelpText>
        </FormElement>
    )
}

ChoiceRow.propTypes = {
    translations: PropTypes.object.isRequired,
    form: PropTypes.object.isRequired,
    handleChange: PropTypes.func.isRequired,
    widget_only: PropTypes.bool
}
ChoiceRow.defaultTypes = {
    widget_only: false
}
