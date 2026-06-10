import { Theme } from './settings/types';
import { KomikHubModernCatalog } from './components/generated/KomikHubModernCatalog';

let theme: Theme = 'light';

function App() {
  function setTheme(theme: Theme) {
    if (theme === 'dark') {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  }

  setTheme(theme);

  return (
    <>
      <KomikHubModernCatalog />
    </>
  ); // %EXPORT_STATEMENT%
}

export default App;
