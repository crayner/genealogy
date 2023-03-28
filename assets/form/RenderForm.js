'use strict';

import React from "react"
import PropTypes from 'prop-types'
import InputRow from "./InputRow";
import ChoiceRow from "./ChoiceRow";
import SubmitForm from "./SubmitForm";

export default function FormRender(props) {
    const {
        form,
        translations,
        handleChange,
        handleSave,
        handleClose,
        identifier,
        sections
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
                    return <ChoiceRow key={i} translations={translations} form={child} handleChange={handleChange} />
                    break;
                case 'collection':
                    return (<div key={i}>Collection Here</div>)
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
            <div className='sidebar-form' id={'form_' + form.name + '_' + identifier}>
                <form className={form.name} method={form.method} action={form.action}>
                    {displayChildrenForms(form.children)}
                </form>
            </div>
        );
    } else {
        return null;
    }
}

FormRender.propTypes = {
    translations: PropTypes.object.isRequired,
    handleChange: PropTypes.func.isRequired,
    handleSave: PropTypes.func.isRequired,
    handleClose: PropTypes.func.isRequired,
    identifier: PropTypes.string.isRequired,
    form: PropTypes.object.isRequired,
    sections: PropTypes.object.isRequired
}
