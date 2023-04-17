'use strict';

import React, {Fragment} from 'react'
import PropTypes from 'prop-types'
import {DarkGreenBold, DarkGreenP, Error, ErrorList} from "../component/StyledCSS";

export default function CategoryErrors(props) {
    const {
        translations,
        category,
    } = props;

    function getErrors() {
        const result = category.errors.map((error, i) => {
            return (<Error key={i}>{error}</Error>);
        });
        return (<Fragment>
            <DarkGreenP><DarkGreenBold>{translations['Category Requirements']}</DarkGreenBold></DarkGreenP>
            <ErrorList>{result}</ErrorList>
        </Fragment>);
    }

    if (typeof category.errors !== 'object' || category.errors.length === 0) return null;

    return getErrors();
}

CategoryErrors.propTypes = {
    translations: PropTypes.object.isRequired,
    category: PropTypes.object.isRequired,
};