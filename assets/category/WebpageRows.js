'use strict';

import React, {Fragment} from 'react'
import PropTypes from 'prop-types'
import * as Style from "../component/StyledCSS";
import OpenFormSection from "./OpenFormSection";

export default function WebpageRows(props) {
    const {
        translations,
        category,
        handleOpenForm,
    } = props;

    function getHeader() {
        return (<Style.FlexContainer key={'header'}>
            <Style.Column2/>
            <Style.Column8 className={'withBorder'}><Style.DarkGreenCentreP><Style.DarkGreenBold>{translations.webpages} <span
                style={{float: 'right', paddingRight: '0.5rem'}}><OpenFormSection sectionName={'webpages'}
                                                                                  translations={translations}
                                                                                  handleOpenForm={handleOpenForm}
            /></span></Style.DarkGreenBold></Style.DarkGreenCentreP></Style.Column8>
            <Style.Column2/>
        </Style.FlexContainer>);
    }


    let result = [];
    result.push(getHeader());
    if (Object.keys(category.webpages).length > 0) {
        Object.keys(category.webpages).map(i => {
            const page = category.webpages[i];
            const prompt = (page.prompt === null || page.prompt.length === 0) ? page.name : page.prompt;
            result.push(
                <Style.FlexContainer key={i}>
                    <Style.Column2/>
                    <Style.Column2 className={'withBorder'}><Style.DarkGreenP>{page.name}:</Style.DarkGreenP></Style.Column2>
                    <Style.Column6 className={'withBorder'}><Style.DarkGreenP><Style.DarkGreenA href={page.url} target={'_blank'}>{prompt}</Style.DarkGreenA></Style.DarkGreenP></Style.Column6>
                    <Style.Column2/>
                </Style.FlexContainer>
            );
        });
    }

    return (result);
}

WebpageRows.propTypes = {
    translations: PropTypes.object.isRequired,
    category: PropTypes.object.isRequired,
    handleOpenForm: PropTypes.func.isRequired,
};