import React from 'react';
import { Container, Typography, Box } from '@material-ui/core';
import WordPressSetup from './components/WordPressSetup';
import PluginSearch from './components/PluginSearch';
import PluginAnalysis from './components/PluginAnalysis';
import EnvironmentVariables from './components/EnvironmentVariables';

function App() {
  return (
    <Container>
      <Typography variant="h3" component="h1" gutterBottom>
        FakeWoo Dashboard
      </Typography>
      <Box mb={4}>
        <WordPressSetup />
      </Box>
      <Box mb={4}>
        <PluginSearch />
      </Box>
      <Box mb={4}>
        <PluginAnalysis />
      </Box>
      <Box mb={4}>
        <EnvironmentVariables />
      </Box>
    </Container>
  );
}

export default App;
