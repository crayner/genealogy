'use strict';

import React from "react"
import PropTypes from 'prop-types'
import styled from 'styled-components';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { solid, regular, brands, icon } from '@fortawesome/fontawesome-svg-core/import.macro';

export const FormElement = styled.div`
    flex: 1;
`
const DarkGreenSpan = styled.span`
    color: darkgreen;
`
const DarkRedSpan = styled.span`
    color: darkred;
`
const Rightp = styled.p` 
    text-align: right;
`
export default function SubmitForm(props) {
    const {
        onClick,
        closeForm,
        translations,
        section,
        form
    } = props;

    const closeFormTitle = 'template.close.' + section;
    const saveFormTitle = 'template.save.' + section;
    return (
        <FormElement>
            <Rightp>
                <DarkGreenSpan><FontAwesomeIcon icon={solid('piggy-bank')} onClick={(e) => onClick(section)} title={translations[saveFormTitle]} /></DarkGreenSpan>&nbsp;&nbsp;
                <DarkRedSpan><FontAwesomeIcon icon={solid('circle-xmark')} title={translations[closeFormTitle]} onClick={(e) => closeForm(section)} /></DarkRedSpan>
            </Rightp>
        </FormElement>
    )
}

SubmitForm.propTypes = {
    onClick: PropTypes.func.isRequired,
    closeForm: PropTypes.func.isRequired,
    form: PropTypes.object.isRequired,
    translations: PropTypes.object.isRequired,
    section: PropTypes.string.isRequired,
}

