'use strict';

import React from "react"
import PropTypes from 'prop-types'
import {getChoices} from './ChoiceRow'
import Autocomplete from "./Autocomplete";
import {getForm} from "./ChoiceRow";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { solid, regular, brands, icon } from '@fortawesome/fontawesome-svg-core/import.macro';
import {FormElement, DarkGreenListP, FormElementLabel, HelpText, OrangeSpan} from "../component/StyledCSS";
import PrototypeRender from "./PrototypeRender";


export default function CollectionRow(props) {
    const {
        form,
        translations,
        handleFormChange,
        removeParentCategory,
        fetchChoices,
        functions,
        section,
        template,
    } = props;

    if (typeof form.prototype === 'object' && typeof form.prototype.children === 'object') {
        return <PrototypeRender translations={translations} identifier={section} form={form} template={template} {...functions} />
    }
    function getExistingValues(form){
        console.log(form);
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
    template: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
    form: PropTypes.object.isRequired,
    handleFormChange: PropTypes.func.isRequired,
    removeParentCategory: PropTypes.func.isRequired,
    fetchChoices: PropTypes.func.isRequired,
    section: PropTypes.string.isRequired,
}

