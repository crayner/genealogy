'use strict';

import React from 'react'
import PropTypes from 'prop-types'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { solid, regular, brands, icon } from '@fortawesome/fontawesome-svg-core/import.macro'

export default function OpenFormSection(props) {
    const {
        sectionName,
        translations,
        handleOpenForm,
    } = props;

    let openName = "template.open." + sectionName

    return (
        <FontAwesomeIcon icon={solid('pencil')}
                         title={translations[openName]}
                         onClick={(e) => handleOpenForm(sectionName)}
        />
    );
}

OpenFormSection.propTypes = {
    sectionName: PropTypes.string.isRequired,
    translations: PropTypes.object.isRequired,
    handleOpenForm: PropTypes.func.isRequired,
};
