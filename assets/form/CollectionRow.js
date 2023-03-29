'use strict';

import React from "react"
import PropTypes from 'prop-types'
import {FormElement, HelpText, FormElementLabel} from './InputRow'
import {getRequiredAttribute, getChoices} from './ChoiceRow'
import Autocomplete from "./Autocomplete";

export default function CollectionRow(props) {
    const {
        form,
        translations,
        widget_only,
        handleChange
    } = props;

    console.log(form)
    return (
        <FormElement>
            <FormElementLabel htmlFor={form.id}>{form.label}</FormElementLabel>
            <br />
            <Autocomplete onChange={(e) => handleChange(e, form)} type={form.type} id={form.id} name={form.full_name} required={getRequiredAttribute(form)} defaultValue={form.value} >{getChoices(form)}</Autocomplete>
            <br />
            <HelpText id={`${form.id}_help`} className="help-text">{form.help}</HelpText>
        </FormElement>
    )
}

CollectionRow.propTypes = {
    translations: PropTypes.object.isRequired,
    form: PropTypes.object.isRequired,
    handleChange: PropTypes.func.isRequired,
    widget_only: PropTypes.bool
}
CollectionRow.defaultTypes = {
    widget_only: false
}
