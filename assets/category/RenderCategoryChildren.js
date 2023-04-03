'use strict';

import React, {Fragment} from 'react'
import PropTypes from 'prop-types'
import {DarkGreenP, H3} from "../component/StyledCSS";

export default function RenderCategoryChildren(props) {
    const {
        children,
        translations
    } = props;
    
    function RenderChildren()
    {
        if (children.length === 0) {
            return null;
        }

        const result = Object.keys(children).map(i => {
            i = Number(i);
            const child = children[i];
            if (children.length - 1 === i) {
                return (<Fragment key={i}><a href={child.path}>{child.name}</a></Fragment>)
            }
            return (<Fragment key={i}><a href={child.path}>{child.name}</a> | </Fragment>)
        })

        return (<Fragment>
            <H3>{translations['subCategories']} ({children.length}):</H3>
            <DarkGreenP>{result}</DarkGreenP>
        </Fragment>)
    }

    return (
        <Fragment>{RenderChildren()}</Fragment>
    );

}

RenderCategoryChildren.propTypes = {
    translations: PropTypes.object.isRequired,
    children: PropTypes.oneOfType([
        PropTypes.object,
        PropTypes.array,
    ]).isRequired,
};