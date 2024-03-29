'use strict';

import React from "react"
import PropTypes from 'prop-types'
import InputRow from "./InputRow";
import ChoiceRow from "./ChoiceRow";
import SubmitForm from "./SubmitForm";
import CollectionRow from "./CollectionRow";
import {SidebarForm} from "../component/StyledCSS";

export default function FormRender(props) {
    const {
        form,
        translations,
        handleChange,
        handleSave,
        handleClose,
        handleFormChange,
        fetchChoices,
        identifier,
        sections,
        functions,
    } = props;

    function displayChildrenForms(children) {
        return children.map((child, i) => {
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
                                          functions={functions}
                                          template={form.template[identifier]}
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

    if (sections[identifier]) {
        return (
            <SidebarForm id={'form_' + form.name + '_' + identifier}>
                <form className={form.name} method={form.method} id={form.id}>
                    {displayChildrenForms(form.children)}
                </form>
            </SidebarForm>
        );
    } else {
        return null;
    }
}

FormRender.propTypes = {
    translations: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
    handleChange: PropTypes.func.isRequired,
    handleFormChange: PropTypes.func.isRequired,
    handleSave: PropTypes.func.isRequired,
    handleClose: PropTypes.func.isRequired,
    fetchChoices: PropTypes.func.isRequired,
    identifier: PropTypes.string.isRequired,
    form: PropTypes.object.isRequired,
    sections: PropTypes.object.isRequired,
}
