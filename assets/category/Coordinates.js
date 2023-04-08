'use strict';

import React from 'react'
import PropTypes from 'prop-types'
import * as Style from "../component/StyledCSS";
import OpenFormSection from "./OpenFormSection";
import {DarkGreenA} from "../component/StyledCSS";

export default function Coordinates(props) {
    const {
        translations,
        category,
        handleOpenForm,
    } = props;

    if (!category.coordinates) return null;

    function getGoogleLink() {
        let link= 'https://maps.google.com/maps/@{lat},{long},{zoom}z/data=!3m1!1e3';
        link = link.replace('{lat}', category.coordinates.latitude);
        link = link.replace('{long}', category.coordinates.longitude);
        link = link.replace('{zoom}', category.coordinates.zoom);
        return link;
    }

    function getOpenStreetMapLink() {
        let link= 'https://www.openstreetmap.org/#map={zoom}/{lat}/{long}';
        link = link.replace('{lat}', category.coordinates.latitude);
        link = link.replace('{long}', category.coordinates.longitude);
        link = link.replace('{zoom}', category.coordinates.zoom);
        return link;
    }

    return (
        <Style.FlexContainer>
            <Style.Column2 />
            <Style.Column2 className={'withBorder'}><Style.DarkGreenP>{translations.map}:</Style.DarkGreenP></Style.Column2>
            <Style.Column6 className={'withBorder'}><Style.DarkGreenP><DarkGreenA href={getOpenStreetMapLink()} target={'_blank'}>{translations.openstreetmaps}</DarkGreenA> <DarkGreenA href={getGoogleLink()} target={'_blank'}>{translations.google}</DarkGreenA> <span style={{float: 'right', paddingRight: '0.25rem'}}><OpenFormSection sectionName={'address'}
                                                                                                                                                                  translations={translations}
                                                                                                                                                                  handleOpenForm={handleOpenForm}
            /></span></Style.DarkGreenP></Style.Column6>
            <Style.Column2 />
        </Style.FlexContainer>
    );

}

Coordinates.propTypes = {
    translations: PropTypes.object.isRequired,
    category: PropTypes.object.isRequired,
    handleOpenForm: PropTypes.func.isRequired,
};