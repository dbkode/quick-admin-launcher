import React, { useState, useEffect } from '@wordpress/element';
import './quickal.scss';

const QuickalModal = ({ quickalData }) => {
  const [modal, setModal] = useState(false);
  const [term, setTerm] = useState('');
  const [results, setResults] = useState([]);
  const [selection, setSelection] = useState(0);
  const [spinner, setSpinner] = useState(false);
	const [serverSearchTimeout, setServerSearchTimeout] = useState(null);

  useEffect(() => {
		// Add click event to admin bar button.
		document.querySelector('.quickal-admin-bar > a').addEventListener('click', (e) => {
			setModal(!modal);
			if (!modal) {
				setTimeout(() => {
					document.getElementById('quickal-modal-input').focus();
				}, 100);
			}
			e.preventDefault();
		});

		// Add keydown event to the document.
    const handleKeyDown = (e) => {
			// Bail out if setting the hotkey on settings page.
			if( 'quickal_setting_hotkey_display' === document.activeElement.id ) {
				return;
			}

      if (quickalData.hotkey.key === e.key 
          && quickalData.hotkey.alt === e.altKey
          && quickalData.hotkey.ctrl === e.ctrlKey
          && quickalData.hotkey.shift === e.shiftKey
          && quickalData.hotkey.meta === e.metaKey) {
        setModal(!modal);
        if (!modal) {
          setTimeout(() => {
            document.getElementById('quickal-modal-input').focus();
          }, 100);
        }
        e.preventDefault();
      }

      if (modal && e.key === 'Escape') {
        setModal(false);
        setSpinner(false);
      }

      if (modal && e.key === 'ArrowDown') {
        setSelection((prev) => (prev + 1) % results.length);
      }

      if (modal && e.key === 'ArrowUp') {
        setSelection((prev) => (prev - 1 + results.length) % results.length);
      }

      if (modal && e.key === 'Enter' && results[selection]) {
        window.location = results[selection].link;
      }
    };

    document.addEventListener('keydown', handleKeyDown);

    return () => {
      document.removeEventListener('keydown', handleKeyDown);
    };
  }, [modal, results, selection, quickalData]);

  // Handle search change.
  const searchChange = (e) => {
    const rawValue = e.target.value;
    setTerm(rawValue); // Show exactly what the user typed
    const term = rawValue.toLowerCase().trim(); // Use trimmed for search logic

    if (term.length === 0) {
      setResults([]);
      setSpinner(false);
      return;
    }

    const termParts = term.split(' ');

    const filteredResults = quickalData.adminMenu.filter(item => {
      return termParts.every(termPart => item.term.includes(termPart));
    });

    setResults(filteredResults);
    setSelection(0);

    // Only call the API if term is at least 2 characters
    if (term.length < 2) {
      setSpinner(false);
      return;
    }

    setSpinner(true);
    const termServer = term.replace(' ', '+');
    clearTimeout(serverSearchTimeout);
    setServerSearchTimeout(setTimeout(async () => {
      try {
        const response = await fetch(`${quickalData.rest}/search/${termServer}`, {
          headers: { 
            "X-WP-Nonce": quickalData.nonce,
            "Content-Type": "application/json;charset=utf-8"
          }
        });
        if (!response.ok) {
          setSpinner(false);
          return;
        }
        const responseJson = await response.json();
        if (Array.isArray(responseJson)) {
          setResults(prevResults => [...prevResults, ...responseJson]);
        }
      } catch (err) {
        setSpinner(false);
      }
      setSpinner(false);
    }, 300));
  };

  // Handle mouse over.
  const handleMouseOver = (index) => {
    setSelection(index);
  };

  // Handle close modal.
  const closeModal = () => {
    setModal(false);
    setSpinner(false);
  };

	// Fix input cursor.
	const fixInputCursor = (e) => {
		if ( 'ArrowUp' === e.key || 'ArrowDown' === e.key ) {
			return false;
		}
	}

  return modal ? (
    <div id="quickal-modal-wrapper" onClick={closeModal}>
      <div id="quickal-modal">

				{/* QuickAL Logo */}
				<div className="quickal-ribbon">
					<div className={`quickal-ribbon-content ${spinner ? 'quickal-ribbon-content-loading' : ''}`}>
						<div className="quickal-ribbon-logo"></div>
					</div>
				</div>

				{/* QuickAL input */}
        <input
          id="quickal-modal-input"
          type="text"
          value={term}
          onChange={searchChange}
					onKeyDown={fixInputCursor}
          placeholder="Search any admin tool or content..."
        />

				{/* QuickAL results */}
        <div className="quickal-modal-dropdown">
          {results.map((item, index) => (
            <div
              key={index}
              className={`quickal-modal-dropdown-item ${index === selection ? 'quickal-selected' : ''}`}
              onMouseOver={() => handleMouseOver(index)}
            >
              {item.icon && item.icon.includes('dashicons-') && (
                <span className={`dashicons-before ${item.icon}`}></span>
              )}
              {item.icon && item.icon.includes('base64') && (
                <span className="quickal-icon-base64" style={{ backgroundImage: `url(${item.icon})` }}></span>
              )}
              <a href={item.link}>
                <span>{item.label}</span>
              </a>
              {item.type && (
                <div className="quickal-result-type">
                  (<span>{item.type}</span>)
                </div>
              )}
            </div>
          ))}
        </div>
      </div>
    </div>
  ) : null;
};

export default QuickalModal; 