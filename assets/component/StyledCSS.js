'use strict';
import React from "react"
import styled, { ThemeProvider } from "styled-components";

const theme = {
    bg: '#f0fff0',
    fg: 'darkgreen',
    orange: 'orange',
    white: 'white',
    black: 'black',
}
export const Theme = ({ children }) => (
    <ThemeProvider theme={theme}>{children}</ThemeProvider>
);
export const Sidebar = styled.div`
    flex: 3;
    padding: 0 8px;
    margin-left: -1px;
    border: 1px solid ${props => props.theme.fg};
};
`
export const Border = styled.div`
    background-color: ${props => props.theme.white};
    flex: 1;
`
export const Column1 = styled.div`
  background-color: ${props => props.theme.bg};
  flex: 1;
`
export const Column2 = styled.div`
    background-color: ${props => props.theme.bg};
    flex: 2;
`
export const Column6 = styled.div`
    background-color: ${props => props.theme.bg};
    flex: 6;
`
export const Main = styled.div`
    flex:6;
    padding: 0 2px;
    border: 1px solid ${props => props.theme.fg};
`
export const MainContainer = styled.div`
  margin: -1px -3px;
  border: 1px solid ${props => props.theme.fg};
  padding: 0 3px 13px;
`
export const Container = styled.div`
    margin: -1px -3px;
    border: 1px solid ${props => props.theme.fg};
    background-color: ${props => props.theme.bg};
`
export const H3 = styled.h3`
  font-size: 1.25em;
  font-weight: bold;
  color: ${props => props.theme.fg};
  margin: 10px 0 0;
`
export const FlexboxContainer = styled.div`
  display: flex;
  clear: both;
  min-height: 75px;
  background-color: ${props => props.theme.bg};
`
export const FlexContainer = styled.div`
  display: flex;
  clear: both;
  background-color: ${props => props.theme.bg};
`
export const Flexbox = styled.div`
    display: flex;
    background-color: ${props => props.theme.bg};
`
export const DarkGreenP = styled.p`
    color: ${props => props.theme.fg};
    padding: 0 0.25rem;
`
export const DarkGreenCentreP = styled.p`
    color: ${props => props.theme.fg};
    text-align: center;
`
export const DarkGreenBold = styled.strong`
    color: ${props => props.theme.fg};
`
export const SidebarForm = styled.div`
    display: flex;
    flex-direction: column;
    padding: 5px 0 10px;
`
export const SuccessP = styled.p`
    line-height: 17px;
    max-height: 17px;
    color: ${props => props.theme.fg};
`

export const FormElement = styled.div`
    flex: 1;
    margin-right: 6px;
    padding-bottom: 3px;
    border-bottom: 1px dotted ${props => props.theme.fg};
`
export const FormElementInput = styled.input`
    height: 25px;
    width: 100%;
    border-radius: 10px;
    border: 1px solid ${props => props.theme.fg};
`
export const FormElementLabel = styled.label`
    font-weight: bold;
    color: ${props => props.theme.fg};
`
export const HelpText = styled.div`
    font-style: italic;
    color: ${props => props.theme.fg};
`
export const NoSuggestions = styled.div`
    color: ${props => props.theme.fg};
    padding: 0.5rem;
`
export const SuggestionList = styled.ul`
    border: 1px solid ${props => props.theme.fg};
    border-top-width: 0;
    list-style: none;
    margin-top: 0;
    margin-left: 5px;
    max-height: 150px;
    overflow-y: auto;
    padding-left: 0;
    width: 100%;
`
export const Suggestion = styled.li`
    line-height: 17px;
    &:hover {
        background-color: ${props => props.theme.fg};
        color: ${props => props.theme.white};
        cursor: pointer;
        font-weight: 700;
    }
`
export const SuggestionActive = styled.li`
    background-color: ${props => props.theme.fg};
    color: ${props => props.theme.white};
    cursor: pointer;
    font-weight: 700;
`
export const OrangeSpan = styled.span`
    color: ${props => props.theme.orange};
`
export const DarkGreenListP = styled.p`
    color: ${props => props.theme.fg};
    line-height: 17px;
    padding-left: 10px;
    margin: 1px 0;
`
export const DarkGreenA = styled.a`
    color: ${props => props.theme.fg};
    &:hover {
        background-color: ${props => props.theme.white};
        color: ${props => props.theme.black};
    }
    &:visited {
        color: ${props => props.theme.fg};
    }

`