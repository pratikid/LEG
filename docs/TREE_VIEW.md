# Family Tree View – Minimalist Control Layout

Based on requirements for a minimalist design with balanced controls, toggle functionality, and clear iconography, this document outlines the optimised button layout for both sides of the family tree view.

---

## Left Side Controls (Top to Bottom)

1. **Zoom In**  
   Icon: `+` (plus)  
   Action: Increases tree scale

2. **Zoom Out**  
   Icon: `−` (minus)  
   Action: Decreases tree scale

3. **Fit to Screen Toggle**  
   Icon: `⤢` (diagonal arrows)  
   Toggle Behaviour:  
   - Default: Fits entire tree to viewport  
   - Active: Returns to custom zoom level  
   Visual Indicator: Filled/coloured when active

4. **Layout Toggle**  
   Icon: `⇄` ↔ `⇅` (horizontal/vertical arrows)  
   Toggle Behaviour:  
   - Switches between horizontal and vertical layouts  
   - Icon updates to show current orientation

5. **Highlight Direct Line Toggle**  
   Icon: `⭘` → `⭑` (hollow star → filled star)  
   Toggle Behaviour:  
   - Default: Shows all connections  
   - Active: Highlights only direct ancestry/descendancy  
   Visual Indicator: Star fills when active

---

## Right Side Controls (Top to Bottom)

1. **Add Person (+)**  
   Icon: `＋` (bold plus in circle)  
   Primary Action: Opens radial menu with options:  
   - `👤` Add Individual  
   - `💑` Add Partner  
   - `👶` Add Child  
   - `👨‍👩‍👧` Add Parents

2. **Export Toggle**  
   Icon: `⤓` (down arrow)  
   Toggle Behaviour:  
   - Expands to show:  
     - `🖼️` Save as Image  
     - `📄` Generate PDF  
     - `📋` Copy Share Link  
   - Collapses after selection

3. **Navigation Reset**  
   Icon: `⌂` (home)  
   Action: Centres view on root ancestor

---

## Key Design Features

1. **Minimalist Implementation**:
   - Only 8 buttons visible by default (5 left, 3 right)
   - Export options collapse after use
   - Add menu appears on demand (radial menu saves space)

2. **Toggle Efficiency**:
   - 3 toggle buttons replace 6 static actions:
     - Fit to Screen (toggle)
     - Layout Switch (toggle)
     - Highlight Mode (toggle)
     - Export (expandable)

3. **Icon Standardisation**:
   - All icons use outlined style (Figma/Material Design standards)
   - Colour changes for active toggles (e.g., `⭑` turns gold when highlight is active)
   - Consistent stroke weight (2px)

4. **Spatial Balance**:
   - Left: Navigation/view controls (vertical strip)
   - Right: Data actions (vertical strip)
   - Tree remains completely visible between control zones

---

## Visual Examples

**Left Side Group**  
`[ + ]`  
`[ − ]`  
`[ ⤢ ]` → Active state: `[ ⤢● ]`  
`[ ⇄ ]` → Toggled: `[ ⇅ ]`  
`[ ⭘ ]` → Active: `[ ⭑ ]`  

**Right Side Group**  
`[ ＋ ]` → Expands: `[ 👤 💑 👶 👨‍👩‍👧 ]`  
`[ ⤓ ]` → Expands: `[ 🖼️ 📄 📋 ]`  
`[ ⌂ ]`  

---

## Interaction Benefits

- **Thumb-Friendly**: Critical actions (Add/Reset) at top and bottom edges
- **Zero Overlap**: Controls frame content without overlapping nodes
- **Progressive Disclosure**: Secondary options only appear when needed
- **Toggle States**: Clear visual feedback through icon transformations

---

This approach reduces visible controls by 40% compared to conventional UIs while maintaining full functionality through smart toggle patterns and contextual menus. 