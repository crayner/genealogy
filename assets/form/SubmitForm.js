'use strict';

import React from "react"
import PropTypes from 'prop-types'
import * as Style from "../component/StyledCSS";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { solid, regular, brands, icon } from '@fortawesome/fontawesome-svg-core/import.macro';

export default function SubmitForm(props) {
    const {
        onClick,
        closeForm,
        translations,
        section,
    } = props;

    const closeFormTitle = 'template.close.' + section;
    const saveFormTitle = 'template.save.' + section;
    return (
        <Style.FormElement>
            <Style.Rightp>
                <Style.DarkGreenSpan><FontAwesomeIcon icon={solid('piggy-bank')} onClick={(e) => onClick(section)} title={translations[saveFormTitle]} /></Style.DarkGreenSpan>&nbsp;&nbsp;
                <Style.DarkRedSpan><FontAwesomeIcon icon={solid('circle-xmark')} title={translations[closeFormTitle]} onClick={(e) => closeForm(section)} /></Style.DarkRedSpan>
            </Style.Rightp>
        </Style.FormElement>
    )
}

SubmitForm.propTypes = {
    onClick: PropTypes.func.isRequired,
    closeForm: PropTypes.func.isRequired,
    translations: PropTypes.object.isRequired,
    section: PropTypes.string.isRequired,
}

