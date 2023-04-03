'use strict';

import React from "react"
import PropTypes from 'prop-types'
import {FormElementInput, FormElementLabel, HelpText, FormElement} from "../component/StyledCSS";

export default function InputRow(props) {
    const {
        form,
        translations,
        widget_only,
        handleChange
    } = props;

    function getLabelClass(child) {
        let result = 'form_label';
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

    if (widget_only) {
        return (
            <FormElementInput type={form.type} id={form.id} name={form.full_name} required={getRequiredAttribute(form)} defaultValue={form.value} />
        );
    }

    return (
        <FormElement>
            <FormElementLabel htmlFor={form.id}>{form.label}</FormElementLabel>
            <br />
            <FormElementInput type={form.type} id={form.id} name={form.full_name} required={getRequiredAttribute(form)} defaultValue={form.value} onChange={(e) => handleChange(e, form)} />
            <br />
            <HelpText id={`${form.id}_help`} className="help-text">{form.help}</HelpText>
        </FormElement>
    )
}

InputRow.propTypes = {
    translations: PropTypes.object.isRequired,
    form: PropTypes.object.isRequired,
    handleChange: PropTypes.func.isRequired,
    widget_only: PropTypes.bool
}
InputRow.defaultTypes = {
    widget_only: false
}
