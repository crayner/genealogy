'use strict';

import React, {Fragment} from "react"
import PropTypes from 'prop-types'
import {FormElement, HelpText, FormElementLabel} from './InputRow'
import {getRequiredAttribute, getChoices} from './ChoiceRow'
import Autocomplete from "./Autocomplete";
import {getForm, } from "./ChoiceRow";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { solid, regular, brands, icon } from '@fortawesome/fontawesome-svg-core/import.macro';
import styled from 'styled-components';

const OrangeSpan = styled.span`
    color: Orange;
`
export const DarkGreenListP = styled.p`
    color: #003300;
    line-height: 17px;
    padding-left: 10px;
    margin: 1px 0;
`


export default function CollectionRow(props) {
    const {
        form,
        translations,
        handleFormChange,
        removeParentCategory,
        fetchChoices,
        section
    } = props;

    function getExistingValues(form){
        if (typeof form.value === 'object' && form.value.length > 0) {
            return Object.keys(form.value).map(i => {
                const item = form.value[i];
                return (<DarkGreenListP key={item.value}>{item.label} <OrangeSpan><FontAwesomeIcon icon={solid('eraser')} onClick={(e) => removeParentCategory(section, item.value)} title={translations[removeParentCategory]} /></OrangeSpan></DarkGreenListP>);
            })
        }
        return [];
    }

    return (
        <FormElement>
            <FormElementLabel htmlFor={form.id}>{form.label}</FormElementLabel>
            <br />
            <Autocomplete
                suggestions={getChoices({...form})}
                translations={translations}
                form={getForm(form)}
                handleFormChange={handleFormChange}
                fetchChoices={fetchChoices}
                multiple={true}
                section={section}
            />
            <br />
            <HelpText id={`${form.id}_help`} className="help-text">{form.help}</HelpText><br />
            {getExistingValues(form, translations)}
        </FormElement>
    )
}

CollectionRow.propTypes = {
    translations: PropTypes.object.isRequired,
    form: PropTypes.object.isRequired,
    handleFormChange: PropTypes.func.isRequired,
    removeParentCategory: PropTypes.func.isRequired,
    fetchChoices: PropTypes.func.isRequired,
    section: PropTypes.string.isRequired,
}

