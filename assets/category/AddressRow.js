'use strict';

import React from 'react'
import PropTypes from 'prop-types'
import * as Style from "../component/StyledCSS";
import OpenFormSection from "./OpenFormSection";
import { DarkGreenP } from "../component/StyledCSS";

export default function AddressRow(props) {
    const {
        translations,
        category,
        handleOpenForm,
    } = props;

    if (!category.template.address.display) return null;

    return (
        <Style.FlexContainer>
            <Style.Column2 />
            <Style.Column2 className={'withBorder'}><DarkGreenP>{translations.Address}:</DarkGreenP></Style.Column2>
            <Style.Column6 className={'withBorder'}><DarkGreenP>{category.address} <span style={{float: 'right', paddingRight: '0.25rem'}}><OpenFormSection sectionName={'address'}
                                                                                                                                                                               translations={translations}
                                                                                                                                                                               handleOpenForm={handleOpenForm}
            /></span></DarkGreenP></Style.Column6>
            <Style.Column2 />
        </Style.FlexContainer>
    );

}

AddressRow.propTypes = {
    translations: PropTypes.object.isRequired,
    category: PropTypes.object.isRequired,
    handleOpenForm: PropTypes.func.isRequired,
};