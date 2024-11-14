name: 💡 Feature Request
description: Suggest an idea for Salla CLI
title: "feat: "
body:
  - type: checkboxes
    attributes:
      label: Prerequisites
      description: Please ensure you have completed all of the following.
      options:
        - label: I have searched for [existing issues](https://github.com/SallaApp/Salla-CLI/issues) that already include this feature request, without success.
          required: true
  - type: textarea
    attributes:
      label: Describe the Feature Request
      description: A clear and concise description of what the feature does.
    validations:
      required: true
  - type: textarea
    attributes:
      label: Describe the Use Case
      description: A clear and concise use case for what problem this feature would solve.
    validations:
      required: true
  - type: textarea
    attributes:
      label: Describe Preferred Solution
      description: A clear and concise description of what you how you want this feature to be added to Salla CLI.
  - type: textarea
    attributes:
      label: Describe Alternatives
      description: A clear and concise description of any alternative solutions or features you have considered.
  - type: textarea
    attributes:
      label: Related Code
      description: If you are able to illustrate the feature request with an example, please provide a sample Salla CLI application. Try out our [Getting Started Wizard](https://salla.dev/blog/meet-salla-cli/) to quickly spin up an Salla CLI starter app.
  - type: textarea
    attributes:
      label: Additional Information
      description: List any other information that is relevant to your issue. Stack traces, related issues, suggestions on how to implement, Stack Overflow links, forum links, etc.