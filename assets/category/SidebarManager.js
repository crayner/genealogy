'use strict';

import React, {Fragment} from 'react'
import PropTypes from 'prop-types'
import FormRender from "../form/FormRender";
import {extractFormSection} from "../form/FormManager";
import styled from 'styled-components';
import { FormElement } from "../form/InputRow";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { solid, regular, brands, icon } from '@fortawesome/fontawesome-svg-core/import.macro';

export const SidebarForm = styled.div`
    display: flex;
    flex-direction: column;
    border-bottom: 1px solid #003300;
    padding: 5px 0 10px;
`
export const SuccessP = styled.p`
    line-height: 17px;
    max-height: 17px;
    color: darkgreen;
`

export default function SidebarManager(props) {
    const {
        category,
        translations,
        form,
        functions,
        sections,
        messages,
        clearMessage,
    } = props;

    function splitFormToSections() {
        let result = {};
        Object.keys(form.template).map(i => {
            const child = form.template[i];
            result[child.name] = extractFormSection(form, child.name);
        })
        return result
    }

    function renderSubForms() {
        const subForms = splitFormToSections();
        return Object.keys(subForms).map(i => {
            let child = subForms[i];
            return (<FormRender
                form={child}
                translations={translations}
                identifier={i}
                key={i}
                sections={sections}
                {...functions}
            />)
        })
    }

    function renderMessages() {
        return Object.keys(messages).map(index => {
            const message = messages[index];
            if (message['level'] === 'success') {
                return (<SuccessP key={message.id}>{translations[message.text]} <FontAwesomeIcon
                    icon={solid('circle-xmark')}
                    title={translations.closeMessage}
                    onClick={() => functions['clearMessage'](message.id)}/></SuccessP>);
            }
        });
    }

    return (
        <Fragment>
            {renderMessages()}
            {renderSubForms()}
            <SidebarForm>
                <FormElement><a href={`https://www.wikitree.com/wiki/Category:${category.name}`}  target="_blank">{category.name} {translations['onWikitree']}</a></FormElement>
            </SidebarForm>
        </Fragment>
    );
}

SidebarManager.propTypes = {
    category: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
    translations: PropTypes.object.isRequired,
    form: PropTypes.object.isRequired,
    sections: PropTypes.object.isRequired,
    messages: PropTypes.oneOfType([
        PropTypes.array,
        PropTypes.object,
    ]).isRequired,
    clearMessage: PropTypes.func.isRequired
};