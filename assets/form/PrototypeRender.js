'use strict';

import React, {Fragment} from "react"
import PropTypes from 'prop-types'
import InputRow from "./InputRow";
import ChoiceRow from "./ChoiceRow";
import SubmitForm from "./SubmitForm";
import CollectionRow from "./CollectionRow";
import {DarkGreenBold, DarkGreenListP, FormElement, OrangeSpan} from "../component/StyledCSS";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { solid, regular, brands, icon } from '@fortawesome/fontawesome-svg-core/import.macro';

export default function PrototypeRender(props) {
    const {
        form,
        translations,
        handleChange,
        handleSave,
        handleClose,
        handleFormChange,
        removeExistingItem,
        fetchChoices,
        identifier,
        template,
    } = props;

    function displayChildrenForms(children) {
        return children.map((child, i) => {
            child = {...child};
            child.id = child.id.replace('__name__', 'prototype');
            child.full_name = child.full_name.replace('__name__', 'prototype');
            child.name = child.name.replace('__name__', 'prototype');
            switch (child.type) {
                case 'text':
                    return <InputRow key={i} translations={translations} form={child} handleChange={handleChange} />
                    break;
                case 'hidden':
                    return <InputRow key={i} translations={translations} form={child} widget_only={true} handleChange={handleChange} />
                    break;
                case 'choice':
                    return <ChoiceRow key={i} translations={translations} form={child} handleFormChange={handleFormChange} fetchChoices={fetchChoices} section={identifier} />
                    break;
                case 'collection':
                    return <CollectionRow key={i}
                                          translations={translations}
                                          form={child}
                                          section={identifier}
                                          {...functions}
                    />
                    break;
                case 'submit':
                    return <SubmitForm key={i} onClick={handleSave} form={form} closeForm={handleClose} translations={translations} section={identifier} />
                    break;
                default:
                    return <p key={i}>{child.full_name}</p>
            }
        })
    }

    function displayExistingChildren(children) {
        let result = [];
        children.filter((item, i) => {
            if (item.name !== 'prototype') {
                let label;
                let value;
                let name;
                item.children.map((child, i) => {
                    if (template.prototype['label'] === child.name) {
                        label = child.value;
                    }
                    if (template.prototype['value'] === child.name) {
                        value = child.value;
                    }
                    if (child.name === 'name') {
                        name = child.value;
                    }
                });
                if (label.length === 0) label = name;
                result.push(<DarkGreenListP key={i}>{label} <OrangeSpan><FontAwesomeIcon icon={solid('eraser')} onClick={(e) => removeExistingItem(identifier, value)} title={translations[removeExistingItem]} /></OrangeSpan></DarkGreenListP>);
            }
        })
        return (<FormElement>{result}</FormElement>);
    }

    return (
        <Fragment>
            {displayChildrenForms(form.prototype.children)}
            {displayExistingChildren(form.children)}
        </Fragment>
    );
}

PrototypeRender.propTypes = {
    translations: PropTypes.object.isRequired,
    template: PropTypes.object.isRequired,
    handleChange: PropTypes.func.isRequired,
    handleFormChange: PropTypes.func.isRequired,
    removeExistingItem: PropTypes.func.isRequired,
    handleSave: PropTypes.func.isRequired,
    handleClose: PropTypes.func.isRequired,
    fetchChoices: PropTypes.func.isRequired,
    identifier: PropTypes.string.isRequired,
    form: PropTypes.object.isRequired,
}
